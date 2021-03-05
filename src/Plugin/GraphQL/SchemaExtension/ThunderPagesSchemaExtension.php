<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\thunder_gqls\Wrappers\ThunderQueryConnection;
use Drupal\user\UserInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @SchemaExtension(
 *   id = "thunder_pages",
 *   name = "Content pages",
 *   description = "Adds page types and their fields.",
 *   schema = "thunder"
 * )
 */
class ThunderPagesSchemaExtension extends ThunderSchemaExtensionPluginBase {

  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->registry->addTypeResolver('Page',
      \Closure::fromCallable([
        __CLASS__,
        'resolvePageTypes',
      ])
    );

    $this->resolveFields();
  }

  /**
   * Add article field resolvers.
   */
  protected function resolveFields() {
    /**
     * Article
     */
    $this->resolvePageInterfaceFields('Article');
    $this->resolvePageInterfaceQueryFields('article', 'node');

    $this->registry->addFieldResolver('Article', 'published',
      $this->builder->produce('entity_published')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver('Article', 'author',
      $this->builder->produce('entity_owner')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver('Article', 'seoTitle',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity:node'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('field_seo_title.value'))
    );

    $this->registry->addFieldResolver('Article', 'channel',
      $this->builder->compose(
        $this->builder->produce('property_path')
          ->map('type', $this->builder->fromValue('entity:taxonomy_term'))
          ->map('value', $this->builder->fromParent())
          ->map('path', $this->builder->fromValue('field_channel.entity'))
      )
    );

    $this->registry->addFieldResolver('Article', 'tags',
      $this->builder->produce('entity_reference')
        ->map('entity', $this->builder->fromParent())
        ->map('field', $this->builder->fromValue('field_tags'))
    );

    $this->registry->addFieldResolver('Article', 'content',
      $this->builder->produce('entity_reference_revisions')
        ->map('entity', $this->builder->fromParent())
        ->map('field', $this->builder->fromValue('field_paragraphs'))
    );

    /**
     * Tags
     */
    $this->resolvePageInterfaceFields('Tag');
    $this->resolvePageInterfaceQueryFields('tag', 'taxonomy_term');

    $this->registry->addFieldResolver('Tag', 'author',
      $this->builder->produce('entity_owner')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver('Tag', 'published',
      $this->builder->produce('entity_published')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver('Tag', 'content',
      $this->builder->produce('entity_reference_revisions')
        ->map('entity', $this->builder->fromParent())
        ->map('field', $this->builder->fromValue('field_paragraphs'))
    );

    /**
     * Channel
     */
    $this->resolvePageInterfaceFields('Channel');
    $this->resolvePageInterfaceQueryFields('channel', 'taxonomy_term');

    $this->registry->addFieldResolver('Channel', 'author',
      $this->builder->produce('entity_owner')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver('Channel', 'published',
      $this->builder->produce('entity_published')
        ->map('entity', $this->builder->fromParent())
    );

    $this->registry->addFieldResolver('Channel', 'content',
      $this->builder->produce('entity_reference_revisions')
        ->map('entity', $this->builder->fromParent())
        ->map('field', $this->builder->fromValue('field_paragraphs'))
    );

    /**
     * User
     */
    $this->resolvePageInterfaceFields('User');
    $this->resolvePageInterfaceQueryFields('user', 'node');

    $this->registry->addFieldResolver('User', 'mail',
      $this->builder->produce('property_path')
        ->map('type', $this->builder->fromValue('entity'))
        ->map('value', $this->builder->fromParent())
        ->map('path', $this->builder->fromValue('mail.value'))
    );
  }

  /**
   * Resolves page types.
   *
   * @param mixed $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return string
   *   Response type.
   */
  protected function resolvePageTypes($value, ResolveContext $context, ResolveInfo $info): string {
    if ($value instanceof NodeInterface || $value instanceof TermInterface || $value instanceof UserInterface) {
      return $this->mapBundleToSchemaName($value->bundle());
    }
  }

  /**
   * Add content query field resolvers.
   *
   * @param string $page_type
   *   The page type name.
   * @param string $entity_type
   *   The entity type name.
   */
  protected function resolvePageInterfaceQueryFields(string $page_type, string $entity_type) {
    $this->addConnectionFields('Connection');

    $this->registry->addFieldResolver('Query', $page_type,
      $this->builder->produce('entity_load')
        ->map('type', $this->builder->fromValue($entity_type))
        ->map('bundles', $this->builder->fromValue([$page_type]))
        ->map('id', $this->builder->fromArgument('id'))
    );

    $this->registry->addFieldResolver('Query', 'page_list',
      $this->builder->produce('thunder_page_list_producer')
        ->map('type', $this->builder->fromArgument('type'))
        ->map('bundles', $this->builder->fromArgument('bundles'))
        ->map('offset', $this->builder->fromArgument('offset'))
        ->map('limit', $this->builder->fromArgument('limit'))
        ->map('conditions', $this->builder->fromArgument('conditions'))
        ->map('languages', $this->builder->fromArgument('languages'))
        ->map('ownedOnly', $this->builder->fromArgument('ownedOnly'))
        ->map('allowedFilters', $this->builder->fromArgument('allowedFilters'))
        ->map('sortBy', $this->builder->fromArgument('sortBy'))
    );
  }

  /**
   * Function addConnectionFields - adds the connection fields.
   * @param string $type
   *   The connection type.
   */
  protected function addConnectionFields($type) {
    $this->registry->addFieldResolver($type, 'total',
      $this->builder->callback(function (ThunderQueryConnection $connection) {
        return $connection->total();
      })
    );

    $this->registry->addFieldResolver($type, 'items',
      $this->builder->callback(function (ThunderQueryConnection $connection) {
        return $connection->items();
      })
    );
  }

}
