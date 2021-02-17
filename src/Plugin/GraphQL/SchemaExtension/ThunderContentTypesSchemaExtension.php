<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\thunder_schema\Plugin\GraphQL\Traits\EntitySchemaTrait;

/**
 * @SchemaExtension(
 *   id = "thunder_content_types",
 *   name = "Content types",
 *   description = "Adds content types and their fields.",
 *   schema = "thunder"
 * )
 */
class ThunderContentTypesSchemaExtension extends ThunderSchemaExtensionPluginBase {

  use EntitySchemaTrait;

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->queryFieldResolver();
    $this->fieldResolver();
  }

  /**
   * Add article field resolvers.
   */
  protected function fieldResolver() {
    $this->addContentTypeInterfaceFields('Article');
    $this->addContentTypeInterfaceFields('Tag');
    $this->addContentTypeInterfaceFields('Channel');

    $this->registry->addFieldResolver('Article', 'published',
      $this->builder->produce('entity_published')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver('Article', 'seoTitle',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:node'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_seo_title.value'))
    );

    $this->registry->addFieldResolver('Article', 'channel',
      $this->builder->compose(
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:taxonomy_term'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_channel.entity'))
      )
    );

    $this->registry->addFieldResolver('Article', 'tags',
      $this->builder->produce('entity_reference')
        ->map('entity', $this->builder->fromParent())
        ->map('field', $this->builder->fromValue('field_tags'))
    );

    $this->registry->addFieldResolver('Article', 'content',
      $this->builder->produce('entity_reference_revisions')
        ->map('entity', $this->builder->fromParent())
        ->map('field', $this->builder->fromValue('field_paragraphs'))
    );

  }

  /**
   * Add article query field resolvers.
   */
  protected function queryFieldResolver() {
    $this->registry->addFieldResolver(
      'Query',
      'article',
      $this->builder->produce('entity_load')
        ->map('type', $this->builder->fromValue('node'))
        ->map('bundles', $this->builder->fromValue(['article']))
        ->map('id', $this->builder->fromArgument('id'))
    );

    $this->registry->addFieldResolver('Query', 'channel',
      $this->builder->produce('entity_load')
        ->map('type', $this->builder->fromValue('taxonomy_term'))
        ->map('bundles', $this->builder->fromValue(['channel']))
        ->map('id', $this->builder->fromArgument('id'))
    );

    $this->registry->addFieldResolver('Query', 'tag',
      $this->builder->produce('entity_load')
        ->map('type', $this->builder->fromValue('taxonomy_term'))
        ->map('bundles', $this->builder->fromValue(['tags']))
        ->map('id', $this->builder->fromArgument('id'))
    );
  }

  /**
   * @param string $type
   */
  public function addContentTypeInterfaceFields(string $type) {
    $this->addCommonEntityFields($type);

    $this->registry->addFieldResolver($type, 'name',
      $this->builder->produce('entity_label')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($type, 'url',
      $this->builder->compose(
        $this->builder->produce('entity_url')
          ->map('entity', $this->builder->fromParent()),
        $this->builder->produce('url_path')
          ->map('url', $this->builder->fromParent())
      )
    );

    $this->registry->addFieldResolver($type, 'created',
      $this->builder->produce('entity_created')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($type, 'changed',
      $this->builder->produce('entity_changed')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($type, 'language',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('langcode.value'))
    );

  }

}
