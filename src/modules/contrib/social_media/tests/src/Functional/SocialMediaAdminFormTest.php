<?php

namespace Drupal\Tests\social_media\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Contains test cases for the social media admin form.
 *
 * @group social_media
 */
class SocialMediaAdminFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'social_media',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test case for the social media admin form.
   */
  public function testSocialMediaAdminForm() {
    $assert = $this->assertSession();

    $this->drupalGet('/admin/config/services/social-media');
    $assert->statusCodeEquals(403);

    $this->drupalLogin($this->drupalCreateUser(['administer site configuration']));
    $this->drupalGet('/admin/config/services/social-media');
    $assert->statusCodeEquals(200);

    $this->submitForm([], 'Save configuration');
    $assert->pageTextContains('Your configuration has been saved');
  }

}
