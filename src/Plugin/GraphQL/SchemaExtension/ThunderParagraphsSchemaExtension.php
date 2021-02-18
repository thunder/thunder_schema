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
    $this->addCommonEntityFields('TextParagraph');

    $this->registry->addFieldResolver('TextParagraph', 'text',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:paragraph'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_text.processed'))
    );

    // Image
    $this->addCommonEntityFields('ImageParagraph');

    $this->registry->addFieldResolver('ImageParagraph', 'image',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:paragraph'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_image.entity'))
    );

    // Embed
    $this->addCommonEntityFields('EmbedParagraph');
    $embedEntityProducer = $this->referencedEntityProducer('paragraph', 'field_media');

    $this->registry->addFieldResolver('EmbedParagraph', 'url',
      $this->builder->compose(
        $embedEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_url.value'))
      )
    );

    // Image list
    $this->addCommonEntityFields('ImageListParagraph');
    $mediaEntityProducer = $this->referencedEntityProducer('paragraph', 'field_media');

    $this->registry->addFieldResolver('ImageListParagraph', 'name',
      $this->builder->compose(
        $mediaEntityProducer,
        $this->builder->produce('entity_label')
          ->map('entity', $this->builder->fromParent())
      )
    );

    $this->registry->addFieldResolver('ImageListParagraph', 'images',
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
            return 'EmbedParagraph';
          }
          if (in_array($bundle,['gallery'])) {
            return 'ImageListParagraph';
          }
          return $this->mapBundleToSchemaName($bundle) . 'Paragraph';
        }
      }
    );
  }

}
