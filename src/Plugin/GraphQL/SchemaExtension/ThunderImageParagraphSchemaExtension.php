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
