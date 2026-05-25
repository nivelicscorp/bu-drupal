<?php

namespace Drupal\Tests\telephone_formatter\Unit;

use libphonenumber\NumberParseException;
use Drupal\telephone_formatter\Formatter;
use Drupal\Tests\UnitTestCase;
use libphonenumber\PhoneNumberFormat;

/**
 * Formatter test.
 *
 * @coversDefaultClass Drupal\telephone_formatter\Formatter
 *
 * @group Telephone
 */
class FormatterTest extends UnitTestCase {

  /**
   * Formatter service.
   *
   * @var \Drupal\telephone_formatter\FormatterInterface
   */
  protected $formatterService;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->languageManager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $this->formatterService = new Formatter($this->languageManager);
  }

  /**
   * Test formatter service.
   *
   * ::covers format.
   */
  public function testFormatterService(): void {
    $test_country = 'NO';
    $test_value = '98765432';

    $this->assertEquals('98 76 54 32', $this->formatterService->format($test_value, PhoneNumberFormat::NATIONAL, $test_country));
    $this->assertEquals('+47 98 76 54 32', $this->formatterService->format($test_value, PhoneNumberFormat::INTERNATIONAL, $test_country));
    $this->assertEquals('+4798765432', $this->formatterService->format($test_value, PhoneNumberFormat::E164, $test_country));
    $this->assertEquals('tel:+47-98-76-54-32', $this->formatterService->format($test_value, PhoneNumberFormat::RFC3966, $test_country));
  }

  /**
   * Valid national number but missing region code.
   *
   * ::covers format.
   */
  public function testUnparsableNumber(): void {
    $this->expectException(NumberParseException::class);
    $this->formatterService->format('98765432', PhoneNumberFormat::NATIONAL);
  }

  /**
   * Number was successfully parsed but invalid.
   *
   * ::covers format.
   */
  public function testInvalidNumber(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Number is invalid.');
    $this->formatterService->format('987654320', PhoneNumberFormat::NATIONAL, 'NO');
  }

}
