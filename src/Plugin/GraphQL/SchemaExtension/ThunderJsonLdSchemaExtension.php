<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * Extension to add the JSON-LD script tag to all content page types.
 *
 * @SchemaExtension(
 *   id = "thunder_jsonld",
 *   name = "JSON-LD extension",
 *   description = "Adds the JSON-LD script tag to all content page types.",
 *   schema = "thunder"
 * )
 */
class ThunderJsonLdSchemaExtension extends ThunderSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->addFieldResolver('Article');
    $this->addFieldResolver('BasicPage');
    $this->addFieldResolver('Tags');
    $this->addFieldResolver('Channel');
    $this->addFieldResolver('User');

  }

  /**
   * @param $type
   */
  protected function addFieldResolver($type) {
    $this->addFieldResolverIfNotExists($type, 'jsonld',
      $this->builder->produce('thunder_jsonld')
        ->map('entity', $this->builder->fromParent())
    );
  }

}
