<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\thunder_schema\Plugin\GraphQL\Traits\EntitySchemaTrait;

/**
 * @SchemaExtension(
 *   id = "thunder_paragraphs",
 *   name = "Paragraph extension",
 *   description = "Adds paragraphs and their fields.",
 *   schema = "thunder"
 * )
 */
class ThunderParagraphsSchemaExtension extends ThunderSchemaExtensionPluginBase {

  use EntitySchemaTrait;

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->typeResolver();
    $this->fieldResolver();
  }

  /**
   * Add image paragraph field resolvers.
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
    $imageEntityProducer = $this->referencedEntityProducer('paragraph', 'field_image');

    $this->registry->addFieldResolver('ImageParagraph', 'copyright',
      $this->builder->compose(
        $imageEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_copyright.value'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'description',
      $this->builder->compose(
        $imageEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_description.processed'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'src',
      $this->builder->compose(
        $imageEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.entity')),
        $this->builder->produce('image_url')
          ->map('entity', $this->builder->fromParent())
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'width',
      $this->builder->compose(
        $imageEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.width'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'height',
      $this->builder->compose(
        $imageEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.height'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'title',
      $this->builder->compose(
        $imageEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.title'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'alt',
      $this->builder->compose(
        $imageEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.alt'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'tags',
      $this->builder->compose(
        $imageEntityProducer,
        $this->builder->produce('entity_reference')
          ->map('entity', $this->builder->fromParent())
          ->map('field', $this->builder->fromValue('field_tags'))
        )
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

    $this->registry->addFieldResolver('ImageListParagraph', 'title',
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
