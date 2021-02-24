<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\GraphQL\Response\ResponseInterface;
use Drupal\paragraphs\ParagraphInterface;
use GraphQL\Type\Definition\ResolveInfo;

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

    // Twitter
    $this->addCommonEntityFields('ParagraphTwitter');
    $embedEntityProducer = $this->referencedEntityProducer('paragraph', 'field_media');

    $this->registry->addFieldResolver('ParagraphTwitter', 'url',
      $this->builder->compose(
        $embedEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_url.value'))
      )
    );

    // Instagram
    $this->addCommonEntityFields('ParagraphInstagram');
    $embedEntityProducer = $this->referencedEntityProducer('paragraph', 'field_media');

    $this->registry->addFieldResolver('ParagraphInstagram', 'url',
      $this->builder->compose(
        $embedEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_url.value'))
      )
    );

    // Pinterest
    $this->addCommonEntityFields('ParagraphPinterest');
    $embedEntityProducer = $this->referencedEntityProducer('paragraph', 'field_media');

    $this->registry->addFieldResolver('ParagraphPinterest', 'url',
      $this->builder->compose(
        $embedEntityProducer,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_url.value'))
      )
    );

    // Gallery
    $this->addCommonEntityFields('ParagraphGallery');
    $mediaEntityProducer = $this->referencedEntityProducer('paragraph', 'field_media');

    $this->registry->addFieldResolver('ParagraphGallery', 'name',
      $this->builder->compose(
        $mediaEntityProducer,
        $this->builder->produce('entity_label')
          ->map('entity', $this->builder->fromParent())
      )
    );

    $this->registry->addFieldResolver('ParagraphGallery', 'images',
      $this->builder->compose(
        $mediaEntityProducer,
        $this->builder->produce('entity_reference')
          ->map('entity', $this->builder->fromParent())
          ->map('field', $this->builder->fromValue('field_media_images'))
      )
    );
  }

}
