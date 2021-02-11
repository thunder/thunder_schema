<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\Schema;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema;

/**
 * @Schema(
 *   id = "thunder",
 *   name = "Thunder schema",
 *   extensions = "thunder",
 * )
 */
class ThunderSchema extends ComposableSchema {

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    $id = $this->getPluginId();
    $definition = $this->getPluginDefinition();
    $module = $this->moduleHandler->getModule($definition['provider']);
    $path = 'graphql/' . $id . '.graphqls';
    $file = $module->getPath() . '/' . $path;

    if (!file_exists($file)) {
      throw new InvalidPluginDefinitionException(
        $id,
        sprintf(
          'The module "%s" needs to have a schema definition "%s" in its folder for "%s" to be valid.',
          $module->getName(), $path, $definition['class']));
    }

    return file_get_contents($file) ?: NULL;
  }

}
