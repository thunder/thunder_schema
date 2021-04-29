<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\LocalTaskManager;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\PathProcessor\PathProcessorManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\metatag\MetatagManagerInterface;
use Drupal\typed_data\DataFetcherTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Resolves a typed data value at a given property path.
 *
 * @DataProducer(
 *   id = "thunder_local_tasks",
 *   name = @Translation("Metatags"),
 *   description = @Translation("Resolves metatags."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Metatag values")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Root value")
 *     )
 *   }
 * )
 */
class LocalTasks extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * The rendering service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The metatag manager service.
   *
   * @var \Drupal\Core\Menu\LocalTaskManagerInterface
   */
  protected $localTasksManager;

  protected $currentRequest;
  protected $currentRouteMatch;

  /**
   * The path processor service.
   *
   * @var \Drupal\Core\PathProcessor\InboundPathProcessorInterface
   */
  protected $pathProcessor;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The dynamic router service.
   *
   * @var \Symfony\Component\Routing\Matcher\RequestMatcherInterface
   */
  protected $router;




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
      $container->get('renderer'),
      $container->get('plugin.manager.menu.local_task'),
      $container->get('request_stack'),
      $container->get('current_route_match'),
      $container->get('path.current'),
      $container->get('path_processor_manager'),
      $container->get('router'),
    );
  }

  /**
   * MetaTags constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $localTasksManager
   *   The metatag manager service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    $pluginDefinition,
    RendererInterface $renderer,
    LocalTaskManagerInterface $localTasksManager,
    RequestStack $request,
    CurrentRouteMatch $currentRouteMatch,
    CurrentPathStack $currentPath,
    PathProcessorManager $pathProcessor,
    RouterInterface $router
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->renderer = $renderer;
    $this->localTasksManager = $localTasksManager;
    $this->currentRequest = $request->getCurrentRequest();
    $this->currentRouteMatch = $currentRouteMatch;
    $this->currentPath = $currentPath;
    $this->pathProcessor = $pathProcessor;
    $this->router = $router;
  }

  /**
   * Resolve the metadata.
   *
   * @param mixed $value
   *   The root value.
   * @param string|null $type
   *   The root type.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The cacheable dependency interface.
   *
   * @return mixed
   *   Normalized metatags.
   */
  public function resolve(EntityInterface $entity, RefinableCacheableDependencyInterface $metadata) {

    // routing system, which makes it necessary to fake a route match.

/*
    $this->currentRequest->attributes->set($entity->getEntityTypeId(), $entity);
    $this->currentRequest->attributes->set('_content_moderation_entity_type', $entity->getEntityTypeId());

    $this->currentRouteMatch->resetRouteMatch();
  */

    $context = new RenderContext();
    $result = $this->renderer->executeInRenderContext($context, function () use ($entity) {
      $new_request = Request::create($entity->toUrl()->toString());
      $request_stack = new RequestStack();
      $processed = $this->pathProcessor->processInbound($entity->toUrl()->toUriString(), $new_request);

      $this->currentPath->setPath($processed);
      $this->currentRequest->attributes->add($this->router->matchRequest($new_request));
      $this->currentRouteMatch->resetRouteMatch();
      $request_stack->push($new_request);

      $container = \Drupal::getContainer();
      $container->set('request_stack', $request_stack);



      $tasks = $this->localTasksManager->getLocalTasks($entity->toUrl()->getRouteName());

      $links = [];
      foreach ($tasks['tabs'] as $task) {
        if (!$task['#access']->isForbidden()) {
          $links[] = ['url' => $task['#link']['url']->toString(), 'title' => $task['#link']['title']];
        }
      }
      return $links;
    });
    if (!$context->isEmpty()) {
      $metadata->addCacheableDependency($context->pop());
    }

    file_put_contents('foo.txt', print_r($result,1));


    #var_dump($tasks);
    return $result ?? NULL;
  }

}
