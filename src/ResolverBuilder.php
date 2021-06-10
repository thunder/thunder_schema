<?php

namespace Drupal\thunder_gqls;

use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\graphql\GraphQL\ResolverBuilder as BaseResolverBuilder;

/**
 * ResolverBuilder with more helper functions.
 */
class ResolverBuilder extends BaseResolverBuilder {

  /**
   * Produces an entity_reference field.
   *
   * @param string $field
   *   Name of the filed.
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface|null $entity
   *   Entity to get the field property.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy
   *   The field data producer.
   */
  public function fromEntityReference(string $field, ResolverInterface $entity = NULL) {
    return $this->produce('entity_reference')
      ->map('field', $this->fromValue($field))
      ->map('entity', $entity ?: $this->fromParent());
  }

  /**
   * Produces an entity_reference_revisions field.
   *
   * @param string $field
   *   Name of the filed.
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface|null $entity
   *   Entity to get the field property.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy
   *   The field data producer.
   */
  public function fromEntityReferenceRevisions(string $field, $entity = NULL) {
    return $this->produce('entity_reference_revisions')
      ->map('field', $this->fromValue($field))
      ->map('entity', $entity ?: $this->fromParent());
  }

}
