<?php

namespace Drupal\Tests\thunder_gqls\Functional;

use Drupal\redirect\Entity\Redirect;

/**
 * Test redirects.
 *
 * @group thunder_gqls
 */
class RedirectTest extends ThunderGqlsTestBase {

  /**
   * Tests the jsonld extension.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function testRedirect() {
    $source = '/redirect-url';
    $redirectTo = '/node/6';

    $redirect = Redirect::create();
    $redirect->setSource($source);
    $redirect->setRedirect($redirectTo);
    $redirect->setLanguage('en');
    $redirect->setStatusCode('301');
    $redirect->save();

    $this->drupalLogin($this->graphqlUser);
    $this->runAndTestQuery('redirect');
  }

}
