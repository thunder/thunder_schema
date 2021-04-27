<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\media\MediaInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * The Common types schema extension.
 *
 * @SchemaExtension(
 *   id = "thunder_common",
 *   name = "Common types",
 *   description = "Commonly used type definitions.",
 *   schema = "thunder"
 * )
 */
class ThunderCommonTypesSchemaExtension extends ThunderSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    // Url path field.
    $this->addFieldResolverIfNotExists(
      'Url',
      'path',
      $this->builder->produce('url_path')
        ->map('url', $this->builder->fromParent())
    );

    // Menu id.
    $this->addFieldResolverIfNotExists(
      'Menu',
      'id',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:menu'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('id'))
    );

    // Menu name.
    $this->addFieldResolverIfNotExists(
      'Menu',
      'name',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:menu'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('label'))
    );

    // Menu items.
    $this->addFieldResolverIfNotExists(
      'Menu',
      'items',
      $this->builder->produce('menu_links')
        ->map('menu', $this->builder->fromParent())
    );

    // Menu title.
    $this->addFieldResolverIfNotExists(
      'MenuItem',
      'title',
      $this->builder->produce('menu_link_label')
        ->map(
          'link',
          $this->builder->produce('menu_tree_link')
            ->map('element', $this->builder->fromParent())
        )
    );

    // Menu in active trail.
    $this->addFieldResolverIfNotExists(
      'MenuItem',
      'inActiveTrail',
      $this->builder->produce('menu_tree_in_active_trail')
        ->map('element', $this->builder->fromParent())
    );

    // Menu children.
    $this->addFieldResolverIfNotExists(
      'MenuItem',
      'children',
      $this->builder->produce('menu_tree_subtree')
        ->map('element', $this->builder->fromParent())
    );

    // Menu url.
    $this->addFieldResolverIfNotExists(
      'MenuItem',
      'url',
      $this->builder->produce('menu_link_url')
        ->map(
          'link',
          $this->builder->produce('menu_tree_link')
            ->map('element', $this->builder->fromParent())
        )
    );

  }

}
