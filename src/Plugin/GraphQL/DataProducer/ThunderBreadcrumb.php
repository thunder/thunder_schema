<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Resolves breadcrumbs for an entity.
 *
 * @DataProducer(
 *   id = "thunder_breadcrumb",
 *   name = @Translation("Breadcrumb"),
 *   description = @Translation("Resolves the breadcrumb for an entity."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("The url")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Root value")
 *     )
 *   }
 * )
 */
class ThunderBreadcrumb extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The router.
   *
   * @var \Symfony\Component\Routing\RouterInterface
   */
  private RouterInterface $router;

  /**
   * The breadcrumb manager.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
   */
  private BreadcrumbBuilderInterface $breadcrumbManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('breadcrumb'),
      $container->get('router')
    );
  }

  /**
   * Breadcrumb constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface $breadcrumbManager
   *   The breadcrumb manager.
   * @param \Symfony\Component\Routing\RouterInterface $router
   *   The router.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    $pluginDefinition,
    BreadcrumbBuilderInterface $breadcrumbManager,
    RouterInterface $router
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->breadcrumbManager = $breadcrumbManager;
    $this->router = $router;
  }

  /**
   * Resolve the breadcrumb.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The cacheable dependency interface.
   *
   * @return mixed
   *   The breadcrumb.
   */
  public function resolve(EntityInterface $entity, RefinableCacheableDependencyInterface $metadata) {
    $routName = $entity->toUrl()->getRouteName();
    $routes = $this->router->getRouteCollection();

    $route = $routes->get($routName);
    $routeMatch = new RouteMatch($routName, $route, ['entity' => $entity], ['entity' => $entity->uuid()]);

    $breadCrumbString = '';
    foreach ($this->breadcrumbManager->build($routeMatch)->getLinks() as $link) {
      $breadCrumbString .= $link->toString();
    }
    return $breadCrumbString;
  }

}
