<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\thunder_gqls\Wrappers\QueryConnection;
use GraphQL\Error\UserError;

/**
 * @DataProducer(
 *   id = "thunder_page_list_producer",
 *   name = @Translation("Load entities"),
 *   description = @Translation("Loads a list of entities of a type."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Entity connection")
 *   ),
 *   consumes = {
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Entity type")
 *     ),
 *     "bundles" = @ContextDefinition("any",
 *       label = @Translation("Entity bundles"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
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
 *     "conditions" = @ContextDefinition("any",
 *       label = @Translation("Conditions"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "languages" = @ContextDefinition("string",
 *       label = @Translation("Entity languages"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "allowedFilters" = @ContextDefinition("string",
 *       label = @Translation("Allowed filters"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "ownedOnly" = @ContextDefinition("boolean",
 *       label = @Translation("Query only owned entities"),
 *       required = FALSE,
 *       default_value = FALSE
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
class ThunderPageListProducer extends ThunderListProducerBase {

  const MAX_ITEMS = 100;

  /**
   * Resolves the entity query.
   *
   * @param string $type
   *   Entity type.
   * @param string[] $bundles
   *   List of bundles to be filtered.
   * @param int $offset
   *   Query only entities owned by current user.
   * @param int $limit
   *   Maximum number of queried entities.
   * @param array $conditions
   *   List of fields to be used in conditions to restrict access to data.
   * @param string[] $languages
   *   Languages for queried entities.
   * @param array $allowedFilters
   *   List of fields to be used in conditions to restrict access to data.
   * @param bool $ownedOnly
   *   Query only entities owned by current user.
   * @param array $sortBy
   *   List of sorts.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $cacheContext
   *   The caching context related to the current field.
   *
   * @return \Drupal\thunder_gqls\Wrappers\QueryConnection
   *   The list of ids that match this query.
   *
   * @throws \GraphQL\Error\UserError
   *   No bundles defined for given entity type.
   */
  public function resolve(string $type, array $bundles, int $offset, int $limit, array $conditions, array $languages, array $allowedFilters, bool $ownedOnly, array $sortBy, FieldContext $cacheContext) {

    $query = $this->buildBaseEntityQuery (
      $type,
      $bundles,
      $conditions,
      $languages,
      $allowedFilters,
      $ownedOnly,
      $cacheContext
    );

    $query->currentRevision()->accessCheck();

    if ($limit > static::MAX_ITEMS) {
      throw new UserError(sprintf('Exceeded maximum query limit: %s.', static::MAX_ITEMS));
    }

    $storage = $this->entityTypeManager->getStorage($type);
    $entityType = $storage->getEntityType();

    $query->range($offset, $limit);

    if (isset($sortBy)) {
      foreach ($sortBy as $sort) {
        if (!empty($sort['field'])) {
          if (!empty($sort['direction']) && strtolower($sort['direction']) == 'desc') {
            $direction = 'DESC';
          }
          else {
            $direction = 'ASC';
          }
          $query->sort($sort['field'], $direction);
        }
      }
    }

    $cacheContext->addCacheTags($entityType->getListCacheTags());
    $cacheContext->addCacheContexts($entityType->getListCacheContexts());

    return new QueryConnection($query);
  }

}
