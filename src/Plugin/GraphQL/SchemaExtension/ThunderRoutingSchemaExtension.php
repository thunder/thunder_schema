<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\DataProducerPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The routing schema extension.
 *
 * @SchemaExtension(
 *   id = "thunder_routing",
 *   name = "Routing extension",
 *   description = "Adds routing of URLs.",
 *   schema = "thunder"
 * )
 */
class ThunderRoutingSchemaExtension extends ThunderSchemaExtensionPluginBase {

  /**
   * The data producer plugin manager.
   *
   * @var \Drupal\graphql\Plugin\DataProducerPluginManager
   */
  protected $dataProducerManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $extension = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $extension->setDataProducerManager($container->get('plugin.manager.graphql.data_producer'));
    return $extension;
  }

  /**
   * Set the plugin manager.
   *
   * @param \Drupal\graphql\Plugin\DataProducerPluginManager $pluginManager
   *   The data producer plugin manager.
   */
  protected function setDataProducerManager(DataProducerPluginManager $pluginManager) {
    $this->dataProducerManager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $args = [
      $this->builder->produce('route_load')->map('path', $this->builder->fromArgument('path')),
      $this->builder->produce('route_entity')->map('url', $this->builder->fromParent()),
    ];

    if ($this->dataProducerManager->hasDefinition('access_unpublished_token_set')) {
      array_unshift($args , $this->builder->compose(
        $this->builder->produce('access_unpublished_token_set')
          ->map('token', $this->builder->fromArgument('auHash')),
        )
      );
    }

    $this->addFieldResolverIfNotExists('Query', 'page', $this->builder->compose(...$args));
  }

}
