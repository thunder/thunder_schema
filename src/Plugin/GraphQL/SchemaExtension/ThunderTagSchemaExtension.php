<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * @SchemaExtension(
 *   id = "thunder_article",
 *   name = "Article schema extension",
 *   description = "A schema extension that adds article related fields.",
 *   schema = "thunder"
 * )
 */
class ThunderTagSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->queryFieldResolver();
    $this->typeResolvers();
  }

  /**
   * Add article query field resolvers.
   */
  protected function queryFieldResolver() {
    $this->registry->addFieldResolver('Query', 'tag',
      $this->builder->produce('entity_load')
        ->map('type', $this->builder->fromValue('taxonomy_term'))
        ->map('bundles', $this->builder->fromValue(['tag']))
        ->map('id', $this->builder->fromArgument('id'))
    );
  }

}
