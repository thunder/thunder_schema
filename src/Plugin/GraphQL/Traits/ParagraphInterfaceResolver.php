<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\Traits;

trait ParagraphInterfaceResolver {

  /**
   * @param string $type
   */
  public function addParagraphInterfaceFields(string $type) {

    $this->registry->addFieldResolver($type, 'uuid',
      $this->builder->produce('entity_uuid')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($type, 'id',
      $this->builder->produce('entity_id')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($type, 'type',
      $this->builder->produce('entity_bundle')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($type, 'entity',
      $this->builder->produce('entity_type_id')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver($type, 'label',
      $this->builder->produce('entity_label')
        ->map('entity', $this->builder->fromParent())
    );

  }

}
