<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\thunder_gqls\Wrappers\ImageResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns image meta data.
 *
 * @DataProducer(
 *   id = "thunder_image",
 *   name = @Translation("Image meta data"),
 *   description = @Translation("Returns the meta data of an image entity."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Metadata")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class ThunderImage extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The rendering service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

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
      $container->get('image.factory')
    );
  }

  /**
   * ImageUrl constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Image\ImageFactory $imageFactory
   *   The image factory.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    RendererInterface $renderer,
    ImageFactory $imageFactory
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->renderer = $renderer;
    $this->imageFactory = $imageFactory;
  }

  /**
   * Resolver.
   *
   * @param \Drupal\file\FileInterface $entity
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return string|null
   */
  public function resolve(FileInterface $entity, RefinableCacheableDependencyInterface $metadata) {
    $access = $entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed()) {
      $context = new RenderContext();
      $imageFactory = $this->imageFactory;

      $data = $this->renderer->executeInRenderContext($context, function () use ($entity, $imageFactory) {
        $uri = $entity->getFileUri();
        $image = $imageFactory->get($uri);
        return [
          'src' => file_create_url($uri),
          'width' => $image->getWidth(),
          'height' => $image->getHeight()
        ];
      });

      if (!$context->isEmpty()) {
        $metadata->addCacheableDependency($context->pop());
      }

      return new ImageResponse($data);

    }

    return [
      'src' => '',
      'width' => 0,
      'height' => 0
    ];
  }

}
