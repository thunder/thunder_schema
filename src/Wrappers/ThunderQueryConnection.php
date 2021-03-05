<?php

namespace Drupal\thunder_gqls\Wrappers;

use Drupal\Core\Entity\Query\QueryInterface;
use GraphQL\Deferred;

/**
 * ThunderQueryConnection.
 */
class ThunderQueryConnection {

  /**
   * The query variable.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $query;

  /**
   * ThunderQueryConnection constructor.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The QueryInterface.
   */
  public function __construct(QueryInterface $query) {
    $this->query = $query;
  }

  /**
   * Returns the total number of results.
   *
   * @return int
   */
  public function total() {
    $query = clone $this->query;
    $query->range(NULL, NULL)->count();
    return $query->execute();
  }

  /**
   * Returns the result items.
   *
   * @return array|\GraphQL\Deferred
   */
  public function items() {
    $result = $this->query->execute();
    if (empty($result)) {
      return [];
    }

    $buffer = \Drupal::service('graphql.buffer.entity');
    $callback = $buffer->add($this->query->getEntityTypeId(), array_values($result));
    return new Deferred(function () use ($callback) {
      return $callback();
    });
  }

}
