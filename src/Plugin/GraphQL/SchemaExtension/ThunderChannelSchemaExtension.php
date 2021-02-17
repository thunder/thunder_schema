<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * @SchemaExtension(
 *   id = "thunder_channel",
 *   name = "Channel taxonomy extension",
 *   description = "A schema extension that adds channel related fields.",
 *   schema = "thunder"
 * )
 */
class ThunderChannelSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->queryFieldResolver();
    $this->fieldResolver();
  }

  /**
   * Add article field resolvers.
   */
  protected function fieldResolver() {
    $this->addContentTypeInterfaceFields('Channel');
  }


  /**
   * Add channel query field resolvers.
   */
  protected function queryFieldResolver() {
    $this->registry->addFieldResolver('Query', 'channel',
      $this->builder->produce('entity_load')
        ->map('type', $this->builder->fromValue('taxonomy_term'))
        ->map('bundles', $this->builder->fromValue(['channel']))
        ->map('id', $this->builder->fromArgument('id'))
    );
  }

}
