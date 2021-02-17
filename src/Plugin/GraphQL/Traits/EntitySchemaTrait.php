<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\Traits;

trait EntitySchemaTrait {

  /**
   * Add fields common to all content entity types.
   *
   * @param string $entityTypeId
   *   The entity type id.
   */
  public function addCommonEntityFields(string $entityTypeId) {

    $this->registry->addFieldResolver($entityTypeId, 'uuid',
      $this->builder->produce('entity_uuid')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($entityTypeId, 'id',
      $this->builder->produce('entity_id')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($entityTypeId, 'type',
      $this->builder->produce('entity_bundle')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($entityTypeId, 'entity',
      $this->builder->produce('entity_type_id')
        ->map('entity', $this->builder->fromParent())
    );

  }

}
