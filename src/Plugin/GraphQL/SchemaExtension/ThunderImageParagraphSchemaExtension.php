<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * @SchemaExtension(
 *   id = "thunder_image_paragraph",
 *   name = "Image paragraph extension",
 *   description = "A schema extension that adds image paragraph related fields.",
 *   schema = "thunder"
 * )
 */
class ThunderImageParagraphSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->typeResolver();
    $this->fieldResolver();
  }

  /**
   * Add image paragraph field resolvers.
   */
  protected function fieldResolver() {
    $this->addParagraphInterfaceFields('ImageParagraph');

    $producedMediaEntity = $this->builder->produce('property_path')
      ->map('type', $this->builder->fromValue('entity:paragraph'))
      ->map('value', $this->builder->fromParent())
      ->map('path', $this->builder->fromValue('field_image.entity'));

    $this->registry->addFieldResolver('ImageParagraph', 'copyright',
      $this->builder->compose(
        $producedMediaEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_copyright.value'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'description',
      $this->builder->compose(
        $producedMediaEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_description.processed'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'src',
      $this->builder->compose(
        $producedMediaEntity,
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
        $producedMediaEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.width'))
      )
    );

    $this->registry->addFieldResolver('ImageParagraph', 'height',
      $this->builder->compose(
        $producedMediaEntity,
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:media'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_image.height'))
      )
    );

  }

  /**
   * Add image paragraph type.
   */
  protected function typeResolver() {
    $this->registry->addTypeResolver(
      'Paragraph',
      function ($value) {
        if ($value instanceof ParagraphInterface && $value->bundle() === 'image'){
           return 'ImageParagraph';
        }
      }
    );
  }

}
