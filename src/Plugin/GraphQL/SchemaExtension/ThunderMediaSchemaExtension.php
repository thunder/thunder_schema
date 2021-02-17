<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\media\MediaInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\thunder_schema\Plugin\GraphQL\Traits\EntitySchemaTrait;

/**
 * @SchemaExtension(
 *   id = "thunder_media",
 *   name = "Media extension",
 *   description = "Adds media entities and their fields.",
 *   schema = "thunder"
 * )
 */
class ThunderMediaSchemaExtension extends ThunderSchemaExtensionPluginBase {

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

    // Image
    $this->addCommonEntityFields('Image');
    $this->registry->addFieldResolver('Image', 'copyright',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:media'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_copyright.value'))
    );

    $this->registry->addFieldResolver('Image', 'description',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:media'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_description.processed'))
    );

    $this->registry->addFieldResolver('Image', 'src',
      $this->builder->compose(
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.entity')),
        $this->builder->produce('image_url')
          ->map('entity', $this->builder->fromParent())
      )
    );

    $this->registry->addFieldResolver('Image', 'width',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:media'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_image.width'))
    );

    $this->registry->addFieldResolver('Image', 'height',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:media'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_image.height'))
    );

    $this->registry->addFieldResolver('Image', 'title',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:media'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_image.title'))
    );

    $this->registry->addFieldResolver('Image', 'alt',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:media'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_image.alt'))
    );

    $this->registry->addFieldResolver('Image', 'tags',
      $this->builder->produce('entity_reference')
        ->map('entity', $this->builder->fromParent())
        ->map('field', $this->builder->fromValue('field_tags'))
    );

    // Embed
    $this->addCommonEntityFields('Embed');
    $this->registry->addFieldResolver('Embed', 'url',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:media'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_url.value'))
    );
  }

  /**
   * Add Paragraph types.
   */
  protected function typeResolver() {
    $this->registry->addTypeResolver(
      'Media',
      function ($value) {
        if ($value instanceof MediaInterface) {
          $bundle = $value->bundle();
          if (in_array($bundle,['twitter','pinterest','instagram'])) {
            return 'Embed';
          }
          if (in_array($bundle,['gallery'])) {
            return 'Image';
          }
          return $this->mapBundleToSchemaName($bundle);
        }
      }
    );

  }

}
