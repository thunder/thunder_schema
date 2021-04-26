<?php

namespace Drupal\thunder_gqls\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;

class SchemaExtendEvent extends Event {

  const EVENT_NAME = 'thunder_gqls_schema_extend';

  /**
   * The user account.
   *
   * @var \Drupal\user\UserInterface
   */
  public $registry;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $account
   *   The account of the user logged in.
   */
  public function __construct(ResolverRegistryInterface $registry) {
    $this->registry = $registry;
  }
}
