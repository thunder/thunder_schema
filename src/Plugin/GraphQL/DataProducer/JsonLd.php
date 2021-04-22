<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\schema_metatag\SchemaMetatagManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resolves a jsonld string for en entity.
 *
 * @DataProducer(
 *   id = "thunder_jsonld",
 *   name = @Translation("JsonLD"),
 *   description = @Translation("Resolves json+ld."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("JsonLd script tag")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Root value")
 *     )
 *   }
 * )
 */
class JsonLd extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The rendering service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The schema metatag manager service.
   *
   * @var \Drupal\schema_metatag\SchemaMetatagManagerInterface
   */
  protected $schemaMetatagManager;

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
      $container->get('module_handler'),
      $container->get('schema_metatag.schema_metatag_manager'),
    );
  }

  /**
   * JsonLd constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\schema_metatag\SchemaMetatagManagerInterface $schemaMetatagManager
   *   The schema metatag manager service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    $pluginDefinition,
    RendererInterface $renderer,
    ModuleHandlerInterface $moduleHandler,
    SchemaMetatagManagerInterface $schemaMetatagManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->renderer = $renderer;

    $this->schemaMetatagManager = $schemaMetatagManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Resolve the jsonld script string.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The cacheable dependency interface.
   *
   * @return mixed
   *   Normalized metatags.
   */
  public function resolve(EntityInterface $entity, RefinableCacheableDependencyInterface $metadata) {
    if (!$this->moduleHandler->moduleExists('schema_metatag') || !($entity instanceof ContentEntityInterface)) {
      return '';
    }
    $context = new RenderContext();
    $result = $this->renderer->executeInRenderContext($context, function () use ($entity) {
      return $this->schemaMetatagManager->getRenderedJsonld($entity);
    });

    if (!$context->isEmpty()) {
      $metadata->addCacheableDependency($context->pop());
    }

    return $result ?? '';
  }

}
