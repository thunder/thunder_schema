<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\Schema;

use Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;

/**
 * @Schema(
 *   id = "thunder",
 *   name = "Thunder composable schema"
 * )
 */
class ThunderSchema extends ComposableSchema {

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    return SdlSchemaPluginBase::getSchemaDefinition();
  }

}
