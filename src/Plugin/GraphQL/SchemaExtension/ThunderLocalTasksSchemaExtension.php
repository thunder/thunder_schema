<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * Extension to add the JSON-LD script tag query.
 *
 * @SchemaExtension(
 *   id = "thunder_local_tasks",
 *   name = "Local tasks extension",
 *   description = "Adds the JSON-LD script tag query.",
 *   schema = "thunder"
 * )
 */
class ThunderLocalTasksSchemaExtension extends ThunderSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->addFieldResolverIfNotExists('Query', 'local_tasks', $this->builder->compose(
      $this->builder->produce('route_load')
        ->map('path', $this->builder->fromArgument('path')),
      $this->builder->produce('route_entity')
        ->map('url', $this->builder->fromParent()),
      $this->builder->produce('thunder_local_tasks')
        ->map('entity', $this->builder->fromParent())
    ));
  }

}
