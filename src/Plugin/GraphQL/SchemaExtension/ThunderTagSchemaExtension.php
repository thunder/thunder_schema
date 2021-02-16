<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * @SchemaExtension(
 *   id = "thunder_tag",
 *   name = "Tag schema extension",
 *   description = "A schema extension that adds tag related fields.",
 *   schema = "thunder"
 * )
 */
class ThunderTagSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->queryFieldResolver();
    $this->typeResolvers('taxonomy_term', 'channel');
    $this->fieldResolver();
  }

  /**
   * Add article field resolvers.
   */
  protected function fieldResolver() {
    $this->addContentTypeInterfaceFields('Tag', $this->registry, $this->builder);
  }


  /**
   * Add tag query field resolvers.
   */
  protected function queryFieldResolver() {
    $this->registry->addFieldResolver('Query', 'tag',
      $this->builder->produce('entity_load')
        ->map('type', $this->builder->fromValue('taxonomy_term'))
        ->map('bundles', $this->builder->fromValue(['tags']))
        ->map('id', $this->builder->fromArgument('id'))
    );
  }

}
