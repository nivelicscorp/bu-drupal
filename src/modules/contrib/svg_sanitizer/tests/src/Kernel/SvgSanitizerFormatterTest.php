<?php

namespace Drupal\Tests\svg_sanitizer\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Defines a class for testing svg sanitizer output.
 *
 * @group svg_sanitizer
 */
class SvgSanitizerFormatterTest extends KernelTestBase {

  /**
   * The test field name.
   *
   * @var string
   */
  protected $fieldName = 'field_test';

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId = 'entity_test';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'user',
    'system',
    'field',
    'text',
    'entity_test',
    'field_test',
    'svg_sanitizer',
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema($this->entityTypeId);
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);

    EntityViewDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
    ])->save();
    FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => $this->entityTypeId,
      'type' => 'file',
    ])->save();
    FieldConfig::create([
      'entity_type' => $this->entityTypeId,
      'field_name' => $this->fieldName,
      'bundle' => $this->entityTypeId,
    ])->save();
  }

  /**
   * Get and prepare the output of a field.
   *
   * @param string $filepath
   *   Filepath.
   * @param array $settings
   *   An array of formatter settings.
   *
   * @return string
   *   The rendered prepared field output.
   */
  protected function getPreparedFieldOutput(string $filepath, array $settings): string {
    $file_system = \Drupal::service('file_system');
    $file_system->copy($filepath, PublicStream::basePath());
    $file = File::create([
      'uri' => 'public://' . basename($filepath),
    ]);
    $file->save();
    $entity = EntityTest::create();
    $entity->{$this->fieldName} = $file;
    $entity->save();

    $field_output = $this->container->get('renderer')
      ->executeInRenderContext(new RenderContext(), function () use ($entity, $settings) {
        return $entity->{$this->fieldName}->view($settings);
      });

    return $this->stripWhitespace((string) $field_output[0]['#markup']);
  }

  /**
   * Remove HTML whitespace from a string.
   *
   * @param string $string
   *   The input string.
   *
   * @return string
   *   The whitespace cleaned string.
   */
  protected function stripWhitespace(string $string): string {
    $no_whitespace = preg_replace('/\s{2,}/', '', $string);
    $no_whitespace = str_replace("\n", '', $no_whitespace);
    return $no_whitespace;
  }

  /**
   * Tests formatter output.
   *
   * @param string $filename
   *   Filepath to SVG file, relative to drupal root.
   * @param array $settings
   *   Formatter settings.
   * @param array $expect_removed
   *   Removed selectors.
   * @param array $expect_present
   *   Present selectors.
   *
   * @dataProvider providerSanitizer
   */
  public function testSanitizer(string $filename, array $settings, array $expect_removed, array $expect_present): void {
    $output = $this->getPreparedFieldOutput(dirname(__DIR__, 2) . '/fixtures/' . $filename, $settings);
    $crawler = new Crawler($output);
    foreach ($expect_removed as $removed) {
      $this->assertEquals(0, $crawler->filter(sprintf('default|svg default|%s', $removed))->count(), sprintf('%s was removed', $removed));
    }
    foreach ($expect_present as $present) {
      $this->assertGreaterThan(0, $crawler->filter(sprintf('default|svg default|%s', $present))->count(), sprintf('%s was found', $present));
    }
  }

  /**
   * Data provider.
   *
   * @return array
   *   Test cases.
   */
  public static function providerSanitizer(): array {
    return [
      'defaults' => [
        'svgTestOne.svg',
        [
          'type' => 'svg_sanitizer',
          'settings' => [
            'allowedattrs' => '',
            'allowedtags' => '',
          ],
        ],
        [
          'this',
          'script',
          'line[onload]',
        ],
        [
          'line[fill=none]',
          'line[x1]',
        ],
      ],
      'additional' => [
        'svgTestOne.svg',
        [
          'type' => 'svg_sanitizer',
          'settings' => [
            'allowedattrs' => '',
            'allowedtags' => 'this',
          ],
        ],
        [
          'script',
          'line[onload]',
        ],
        [
          'this',
          'line[fill=none]',
          'line[x1]',
        ],
      ],
    ];
  }

}
