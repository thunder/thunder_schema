<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\thunder_schema\Plugin\GraphQL\Traits\ContentTypeInterfaceResolver;

/**
 * @SchemaExtension(
 *   id = "thunder_article",
 *   name = "Article schema extension",
 *   description = "A schema extension that adds article related fields.",
 *   schema = "thunder"
 * )
 */
class ThunderArticleSchemaExtension extends ThunderSchemaExtensionPluginBase {

  use ContentTypeInterfaceResolver;

  /**
   * Add article field resolvers.
   */
  protected function fieldResolver() {
    $this->addContentTypeInterfaceFields('Article', $this->registry, $this->builder);

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

}
