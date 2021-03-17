<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\thunder_gqls\Wrappers\EntityListResponse;
use GraphQL\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The entity list producer class.
 *
 * @DataProducer(
 *   id = "entity_list",
 *   name = @Translation("Entity list"),
 *   description = @Translation("Loads a list of entities."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Entity list")
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
 *       label = @Translation("Filter conditions"),
 *       multiple = FALSE,
 *       required = FALSE,
 *       default_value = {}
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
 *     ),
 *   }
 * )
 */
class EntityListProducerBase extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  const MAX_ITEMS = 100;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * EntityLoad constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    EntityTypeManager $entityTypeManager,
    AccountInterface $current_user
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
  }

  /**
   * Build base entity query which may be reused for count query as well.
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
   *   List of conditions to filter the entities.
   * @param string[] $languages
   *   Languages for queried entities.
   * @param array $sortBy
   *   List of sorts.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $cacheContext
   *   The caching context related to the current field.
   *
   * @return \Drupal\thunder_gqls\Wrappers\EntityListResponse
   *   Base entity list response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function resolve(string $type, array $bundles, int $offset, int $limit, array $conditions, array $languages, array $sortBy, FieldContext $cacheContext): EntityListResponse {
    if ($limit > static::MAX_ITEMS) {
      throw new UserError(sprintf('Exceeded maximum query limit: %s.', static::MAX_ITEMS));
    }

    $entity_type = $this->entityTypeManager->getStorage($type);
    $query = $entity_type->getQuery();

    $query->currentRevision()->accessCheck();

    // Ensure that access checking is performed on the query.
    $query->currentRevision()->accessCheck(TRUE);

    // Filter entities only of given bundles, if desired.
    if ($bundles) {
      $bundle_key = $entity_type->getEntityType()->getKey('bundle');
      if (!$bundle_key) {
        throw new UserError('No bundles defined for given entity type.');
      }
      $query->condition($bundle_key, $bundles, 'IN');
    }

    // Filter entities by given languages, if desired.
    if ($languages) {
      $query->condition('langcode', $languages, 'IN');
    }

    // Filter by given conditions.
    foreach ($conditions as $condition) {
      $operation = isset($condition['operator']) ? $condition['operator'] : NULL;
      $query->condition($condition['field'], $condition['value'], $operation);
    }

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

    $query->range($offset, $limit);

    $storage = $this->entityTypeManager->getStorage($type);
    $entityType = $storage->getEntityType();

    $cacheContext->addCacheTags($entityType->getListCacheTags());
    $cacheContext->addCacheContexts($entityType->getListCacheContexts());

    return new EntityListResponse($query);
  }

}
