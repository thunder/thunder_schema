<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * @SchemaExtension(
 *   id = "thunder_embed_paragraph",
 *   name = "Embed paragraph extension",
 *   description = "A schema extension that adds image paragraph related fields.",
 *   schema = "thunder"
 * )
 */
class ThunderEmbedParagraphSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->typeResolver();
    $this->fieldResolver();
  }

  /**
   * Add image paragraph field resolvers.
   */
  protected function fieldResolver() {
    $this->addParagraphInterfaceFields('EmbedParagraph');

    $producedMediaEntity = $this->builder->produce('property_path')
      ->map('type', $this->builder->fromValue('entity:paragraph'))
      ->map('value', $this->builder->fromParent())
      ->map('path', $this->builder->fromValue('field_media.entity'));

    $this->registry->addFieldResolver('EmbedParagraph', 'url',
      $this->builder->compose(
        $producedMediaEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_url.value'))
      )
    );
  }

  /**
   * Add embed paragraph type.
   */
  protected function typeResolver() {
    $this->registry->addTypeResolver('Paragraph',
      function ($value) {
        if ($value instanceof ParagraphInterface && in_array($value->bundle(), ['twitter', 'pinterest', 'instagram'])) {
           return 'EmbedParagraph';
        }
      }
    );
  }

}
