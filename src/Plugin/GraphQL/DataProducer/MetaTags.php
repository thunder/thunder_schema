<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\typed_data\DataFetcherTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resolves a typed data value at a given property path.
 *
 * @DataProducer(
 *   id = "thunder_metatags",
 *   name = @Translation("Metatags"),
 *   description = @Translation("Resolves metatags."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Metatag values")
 *   ),
 *   consumes = {
 *     "value" = @ContextDefinition("any",
 *       label = @Translation("Root value")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Root type"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class MetaTags extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * The rendering service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
      $container->get('renderer')
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
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    $pluginDefinition,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->renderer = $renderer;
  }


  /**
   * Resolve the metadata.
   *
   * @param mixed $value
   * @param string|null $type
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   */
  public function resolve($value, ?string $type, RefinableCacheableDependencyInterface $metadata
  ) {
    if (!($value instanceof TypedDataInterface) && !empty($type)) {
      $manager = $this->getTypedDataManager();
      $definition = $manager->createDataDefinition($type);
      $value = $manager->create($definition, $value);
    }

    if (!($value instanceof TypedDataInterface)) {
      throw new \BadMethodCallException('Could not derive typed data type.');
    }

    $bubbleable = new BubbleableMetadata();
    $fetcher = $this->getDataFetcher();

    $context = new RenderContext();
    $path = 'metatag';

    $result = $this->renderer->executeInRenderContext($context, function () use ($fetcher, $value, $path, $bubbleable) {
      return $fetcher->fetchDataByPropertyPath($value, $path, $bubbleable)->getValue();
    });

    if (!$context->isEmpty()) {
      $metadata->addCacheableDependency($context->pop());
    }

    return $result ?? NULL;
  }

}
