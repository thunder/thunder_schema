<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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

    /** @var \Symfony\Component\HttpKernel\HttpKernel $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');

    $requestStack = \Drupal::service('request_stack');


    $url = $entity->toUrl();

    $currentRequest = $requestStack->getCurrentRequest();
    $request = Request::create(
      $url->getInternalPath(),
      'GET',
      [MainContentViewSubscriber::WRAPPER_FORMAT => 'thunder_gqls'],
      $currentRequest->cookies->all(),
      $currentRequest->files->all(),
      $currentRequest->server->all()
    );

    if ($session = $currentRequest->getSession()) {
      $request->setSession($session);
    }

    /** @var \Symfony\Component\HttpFoundation\JsonResponse $response */
    $response = $httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);

    $content = (string)$response->getContent();

    return Json::decode($content)['breadcrumb'];
  }

}
