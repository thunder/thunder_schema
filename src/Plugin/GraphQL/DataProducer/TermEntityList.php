<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;

/**
 * The channel list producer class.
 *
 * @DataProducer(
 *   id = "term_entity_list",
 *   name = @Translation("Term entity list"),
 *   description = @Translation("Loads a list of entities for a term."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Term entity list")
 *   ),
 *   consumes = {
 *     "term" = @ContextDefinition("entity",
 *       label = @Translation("Term entity")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Entity type")
 *     ),
 *     "bundles" = @ContextDefinition("any",
 *       label = @Translation("Entity bundles"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "field" = @ContextDefinition("any",
 *       label = @Translation("The term reference field"),
 *       multiple = FALSE,
 *       required = TRUE
 *     ),
 *     "offset" = @ContextDefinition("integer",
 *       label = @Translation("Offset"),
 *       required = FALSE,
 *       default_value = 0
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       required = FALSE,
 *       default_value = 100
 *     ),
 *     "languages" = @ContextDefinition("string",
 *       label = @Translation("Entity languages"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "sortBy" = @ContextDefinition("any",
 *       label = @Translation("Sorts"),
 *       multiple = TRUE,
 *       default_value = {},
 *       required = FALSE
 *     )
 *   }
 * )
 */
class TermEntityList extends EntityListProducerBase {

  /**
   * {@inheritdoc}
   */
  public function resolve(EntityInterface $term, string $type, array $bundles, string $field, int $offset, int $limit, array $languages, array $sortBy, FieldContext $cacheContext) {
    $conditions = [
      ['field' => $field, 'value' => $term->id()]
    ];

    return parent::resolve(
      $type,
      $bundles,
      $offset,
      $limit,
      $conditions,
      $languages,
      $sortBy,
      $cacheContext
    );

  }

}
