<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\SchemaExtension;

use Drupal\Component\EventDispatcher\Event;
use Drupal\thunder_gqls\Event\SchemaExtendEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Schema extension for page types.
 *
 * @SchemaExtension(
 *   id = "thunder_jsonld",
 *   name = "JsonLd extension",
 *   description = "Adds page types and their fields.",
 *   schema = "thunder"
 * )
 */
class ThunderJsonLdSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public static function getSubscribedEvents() {
    return [
      SchemaExtendEvent::EVENT_NAME => 'extendSchema',
    ];
  }

  public function extendSchema(SchemaExtendEvent $event) {
    $this->addFieldResolverIfNotExists('Article', 'jsonld',
      $this->builder->produce('thunder_jsonld')
        ->map('entity', $this->builder->fromParent())
    );
  }

}
