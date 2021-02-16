<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * @SchemaExtension(
 *   id = "thunder_image",
 *   name = "Image extension",
 *   description = "A schema extension that adds image related fields.",
 *   schema = "thunder"
 * )
 */
class ThunderImageSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->typeResolver();
    $this->fieldResolver();
  }

  /**
   * Add image field resolvers.
   */
  protected function fieldResolver() {
    $this->addContentElementInterfaceFields('Image');
  }

  /**
   * Add Image type.
   */
  protected function typeResolver() {
    $this->registry->addTypeResolver(
      'ContentElement',
      function ($value) {
        if ($value instanceof ParagraphInterface && $value->bundle() === 'image'){
           return 'Image';
        }
      }
    );
  }

}
