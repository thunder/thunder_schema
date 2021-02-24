<?php

namespace Drupal\thunder_schema\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ThunderSchemaExtensionPluginBase extends SdlSchemaExtensionPluginBase {

  /**
   * ResolverRegistryInterface.
   *
   * @var \Drupal\graphql\GraphQL\ResolverRegistryInterface
   */
  protected $registry;

  /**
   * ResolverBuilder.
   *
   * @var \Drupal\graphql\GraphQL\ResolverBuilder
   */
  protected $builder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $plugin->createResolverBuilder();
    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $this->registry = $registry;

    $this->registry->addTypeResolver('ContentType',
      \Closure::fromCallable([
        __CLASS__,
        'resolveContentTypes',
      ])
    );

    $this->registry->addTypeResolver('Media',
      \Closure::fromCallable([
        __CLASS__,
        'resolveMediaTypes',
      ])
    );

    $this->registry->addTypeResolver('Paragraph',
      \Closure::fromCallable([
        __CLASS__,
        'resolveParagraphTypes',
      ])
    );

  }

  /**
   * Add fields common to all content entity types.
   *
   * @param string $entityTypeId
   *   The entity type id.
   */
  public function addCommonEntityFields(string $entityTypeId) {
    $this->registry->addFieldResolver(
      $entityTypeId,
      'uuid',
      $this->builder->produce('entity_uuid')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver(
      $entityTypeId,
      'id',
      $this->builder->produce('entity_id')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver(
      $entityTypeId,
      'entity',
      $this->builder->produce('entity_type_id')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver(
      $entityTypeId,
      'name',
      $this->builder->produce('entity_label')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver(
      $entityTypeId,
      'author',
      $this->builder->produce('entity_owner')
        ->map('entity', $this->builder->fromParent())
    );

  }

  /**
   * Create the ResolverBuilder.
   */
  protected function createResolverBuilder() {
    $this->builder = new ResolverBuilder();
  }

  /**
   * Get the data producer for a referenced entity.
   *
   * @param $parentEntityType
   *   The entity type id of the parent entity.
   * @param $referenceFieldName
   *   The reference field name.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy
   *   The data producer proxy.
   */
  protected function referencedEntityProducer($parentEntityType, $referenceFieldName) : DataProducerProxy {
    return $this->builder->produce('property_path')
      ->map('type', $this->builder->fromValue('entity:' . $parentEntityType))
      ->map('value', $this->builder->fromParent())
      ->map('path', $this->builder->fromValue($referenceFieldName . '.entity'));
  }

  /**
   * Takes the bundle name and returns the schema name.
   *
   * @param string $bundleName
   *   The bundle name.
   *
   * @return string
   *   Returns the mapped bundle name.
   */
  protected function mapBundleToSchemaName(string $bundleName) {
    return str_replace('_', '', ucwords($bundleName, '_'));
  }

  /**
   * Resolves content types.
   *
   * @param mixed $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return string
   *   Response type.
   */
  protected function resolveContentTypes($value, ResolveContext $context, ResolveInfo $info): string {
    if ($value instanceof NodeInterface || $value instanceof TermInterface || $value instanceof UserInterface) {
      return $this->mapBundleToSchemaName($value->bundle());
    }
  }

  /**
   * Resolves paragraph types.
   *
   * @param mixed $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return string
   *   Response type.
   */
  protected function resolveParagraphTypes($value, ResolveContext $context, ResolveInfo $info): string {
    if ($value instanceof ParagraphInterface) {
      $bundle = $value->bundle();
      return 'Paragraph' . $this->mapBundleToSchemaName($value->bundle());
    }
  }

  /**
   * Resolves media types.
   *
   * @param mixed $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return string
   *   Response type.
   */
  protected function resolveMediaTypes($value, ResolveContext $context, ResolveInfo $info): string {
    if ($value instanceof MediaInterface) {
      return $this->mapBundleToSchemaName($value->bundle());
    }
  }

}
