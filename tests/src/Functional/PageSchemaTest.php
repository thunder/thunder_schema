<?php

namespace Drupal\Tests\thunder_gqls\Functional;

use Drupal\Core\Url;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Test the page schemata.
 *
 * @group thunder_gqls
 */
class PageSchemaTest extends ThunderGqlsTestBase {

  /**
   * Tests the article schema.
   */
  public function testArticleSchema() {
    $this->drupalLogin($this->graphqlUser);

    $articleExample = 'article';

    $query = $this->getQueryFromFile($articleExample);
    $variables = $this->getVariablesFromFile($articleExample);

    $response = $this->query($query, $variables);

    $this->assertEquals(200, $response->getStatusCode(), 'Response not 200');

    $responseData = json_decode($response->getBody())->data;
    $expectedData = json_decode($this->getExpectedResponseFromFile($articleExample))->data;

    $this->assertEquals($expectedData->article->name, $responseData->article->name);
  }

}
