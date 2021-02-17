<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\paragraphs\ParagraphInterface;
use GraphQL\Error\Error;

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
   * Add image paragraph field resolvers.
   */
  protected function fieldResolver() {
    // Text
    $this->addParagraphInterfaceFields('TextParagraph');
    $this->registry->addFieldResolver('TextParagraph', 'text',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:paragraph'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_text.processed'))
    );

    // Image
    $this->addParagraphInterfaceFields('ImageParagraph');
    $imageEntity = $this->builder->produce('property_path')
      ->map('type', $this->builder->fromValue('entity:paragraph'))
      ->map('value', $this->builder->fromParent())
      ->map('path', $this->builder->fromValue('field_image.entity'));

    $this->registry->addFieldResolver('ImageParagraph', 'copyright',
      $this->builder->compose(
        $imageEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_copyright.value'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'description',
      $this->builder->compose(
        $imageEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_description.processed'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'src',
      $this->builder->compose(
        $imageEntity,
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
        $imageEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.width'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'height',
      $this->builder->compose(
        $imageEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.height'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'title',
      $this->builder->compose(
        $imageEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.title'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'alt',
      $this->builder->compose(
        $imageEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.alt'))
      )
    );

    // Embed
    $this->addParagraphInterfaceFields('EmbedParagraph');
    $embedEntity = $this->builder->produce('property_path')
      ->map('type', $this->builder->fromValue('entity:paragraph'))
      ->map('value', $this->builder->fromParent())
      ->map('path', $this->builder->fromValue('field_media.entity'));
    $this->registry->addFieldResolver('EmbedParagraph', 'url',
      $this->builder->compose(
        $embedEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_url.value'))
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

  /**
   * Takes the bundle name and returns the schema name.
   *
   * @param string $bundleName
   *   The bundle name.
   *
   * @return string
   *   Returns the mapped bundle name.
   */
  protected function mapBundleToSchemaName(string $bundleName) {
    return str_replace('_', '', ucwords($bundleName, '_'));
  }
}
