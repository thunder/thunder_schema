<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * @SchemaExtension(
 *   id = "thunder_paragraphs",
 *   name = "Paragraph extension",
 *   description = "Adds paragraphs and their fields.",
 *   schema = "thunder"
 * )
 */
class ThunderParagraphsSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->typeResolver();
    $this->fieldResolver();
  }

  /**
   * Add paragraph field resolvers.
   */
  protected function fieldResolver() {
    // Text
    $this->addCommonEntityFields('ParagraphText');

    $this->registry->addFieldResolver('ParagraphText', 'text',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:paragraph'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_text.processed'))
    );

    // Image
    $this->addCommonEntityFields('ParagraphImage');

    $this->registry->addFieldResolver('ParagraphImage', 'image',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:paragraph'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_image.entity'))
    );

    // Embed
    $this->addCommonEntityFields('ParagraphEmbed');
    $embedEntityProducer = $this->referencedEntityProducer('paragraph', 'field_media');

    $this->registry->addFieldResolver('ParagraphEmbed', 'url',
      $this->builder->compose(
        $embedEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_url.value'))
      )
    );

    // Image list
    $this->addCommonEntityFields('ParagraphImageList');
    $mediaEntityProducer = $this->referencedEntityProducer('paragraph', 'field_media');

    $this->registry->addFieldResolver('ParagraphImageList', 'name',
      $this->builder->compose(
        $mediaEntityProducer,
        $this->builder->produce('entity_label')
          ->map('entity', $this->builder->fromParent())
      )
    );

    $this->registry->addFieldResolver('ParagraphImageList', 'images',
      $this->builder->compose(
        $mediaEntityProducer,
        $this->builder->produce('entity_reference')
          ->map('entity', $this->builder->fromParent())
          ->map('field', $this->builder->fromValue('field_media_images'))
      )
    );
  }

  /**
   * Add Paragraph types.
   */
  protected function typeResolver() {
    $this->registry->addTypeResolver(
      'Paragraph',
      function ($value) {
        if ($value instanceof ParagraphInterface) {
          $bundle = $value->bundle();
          if (in_array($bundle,['twitter','pinterest','instagram'])) {
            return 'ParagraphEmbed';
          }
          if (in_array($bundle,['gallery'])) {
            return 'ParagraphImageList';
          }
          return 'Paragraph' . $this->mapBundleToSchemaName($bundle);
        }
      }
    );
  }

}
