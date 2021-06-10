<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\SchemaExtension;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\thunder_gqls\Wrappers\EntityListResponse;
use Drupal\user\UserInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Schema extension for page types.
 *
 * @SchemaExtension(
 *   id = "thunder_pages",
 *   name = "Content pages",
 *   description = "Adds page types and their fields.",
 *   schema = "thunder"
 * )
 */
class ThunderPagesSchemaExtension extends ThunderSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    parent::registerResolvers($registry);

    $this->registry->addTypeResolver('Page',
      \Closure::fromCallable([
        __CLASS__,
        'resolvePageTypes',
      ])
    );
    $this->resolvePageFields();

    $this->registry->addTypeResolver('Media',
      \Closure::fromCallable([
        __CLASS__,
        'resolveMediaTypes',
      ])
    );
    $this->resolveMediaFields();

    $this->registry->addTypeResolver('Paragraph',
      \Closure::fromCallable([
        __CLASS__,
        'resolveParagraphTypes',
      ])
    );
    $this->resolveParagraphFields();
  }

  /**
   * Add article field resolvers.
   */
  protected function resolvePageFields() {
    $this->resolveArticleFields();
    $this->resolveBasicPageFields();
    $this->resolveTagFields();
    $this->resolveChannelFields();
    $this->resolveUserFields();

    // Entity List.
    $this->addFieldResolverIfNotExists('EntityList', 'total',
      $this->builder->callback(function (EntityListResponse $entityList) {
        return $entityList->total();
      })
    );

    $this->addFieldResolverIfNotExists('EntityList', 'items',
      $this->builder->callback(function (EntityListResponse $entityList) {
        return $entityList->items();
      })
    );
  }

  protected function resolveMediaFields() {
    $this->resolveMediaImageFields();
    $this->resolveMediaVideoFields();
  }

  protected function resolveArticleFields() {
    $this->resolvePageInterfaceFields('Article', 'node');
    $this->resolvePageInterfaceQueryFields('article', 'node');

    $this->addFieldResolverIfNotExists('Article', 'seoTitle',
      $this->builder->fromPath('entity', 'field_seo_title.value')
    );

    $this->addFieldResolverIfNotExists('Article', 'channel',
      $this->builder->fromPath('entity', 'field_channel.entity')
    );

    $this->addFieldResolverIfNotExists('Article', 'tags',
      $this->fromEntityReference('field_tags')
    );

    $this->addFieldResolverIfNotExists('Article', 'content',
      $this->fromEntityReferenceRevisions('field_paragraphs')
    );

    $this->addFieldResolverIfNotExists('Article', 'teaser',
      $this->builder->callback(function (ContentEntityInterface $entity) {
        return [
          'image' => $entity->field_teaser_media->entity,
          'text' => $entity->field_teaser_text->value,
        ];
      })
    );
  }

  protected function resolveBasicPageFields() {
    $this->resolvePageInterfaceFields('BasicPage', 'node');

    $this->addFieldResolverIfNotExists('BasicPage', 'content',
      $this->builder->fromPath('entity', 'body.processed')
    );
  }

  protected function resolveTagFields() {
    $this->resolvePageInterfaceFields('Tags', 'taxonomy_term');
    $this->resolvePageInterfaceQueryFields('tags', 'taxonomy_term');

    $this->addFieldResolverIfNotExists('Tags', 'content',
      $this->fromEntityReferenceRevisions('field_paragraphs')
    );

    $this->addFieldResolverIfNotExists('Tags', 'articles',
      $this->builder->produce('entities_with_term')
        ->map('term', $this->builder->fromParent())
        ->map('type', $this->builder->fromValue('node'))
        ->map('bundles', $this->builder->fromValue(['article']))
        ->map('field', $this->builder->fromValue('field_tags'))
        ->map('offset', $this->builder->fromArgument('offset'))
        ->map('limit', $this->builder->fromArgument('limit'))
        ->map('languages', $this->builder->fromArgument('languages'))
        ->map('sortBy', $this->builder->fromValue([
          [
            'field' => 'created',
            'direction' => 'DESC',
          ],
        ]))
    );
  }

  protected function resolveChannelFields() {
    $this->resolvePageInterfaceFields('Channel', 'taxonomy_term');
    $this->resolvePageInterfaceQueryFields('channel', 'taxonomy_term');

    $this->addFieldResolverIfNotExists('Channel', 'content',
      $this->fromEntityReferenceRevisions('field_paragraphs')
    );

    $this->addFieldResolverIfNotExists('Channel', 'parent',
      $this->builder->fromPath('entity', 'parent.entity')
    );

    $this->addFieldResolverIfNotExists('Channel', 'articles',
      $this->builder->produce('entities_with_term')
        ->map('term', $this->builder->fromParent())
        ->map('type', $this->builder->fromValue('node'))
        ->map('bundles', $this->builder->fromValue(['article']))
        ->map('field', $this->builder->fromValue('field_channel'))
        ->map('offset', $this->builder->fromArgument('offset'))
        ->map('limit', $this->builder->fromArgument('limit'))
        ->map('languages', $this->builder->fromArgument('languages'))
        ->map('sortBy', $this->builder->fromValue([
          [
            'field' => 'created',
            'direction' => 'DESC',
          ],
        ]))
        ->map('depth', $this->builder->fromValue(1))
    );
  }

  protected function resolveUserFields() {
    $this->resolvePageInterfaceFields('User', 'user');
    $this->resolvePageInterfaceQueryFields('user', 'node');

    $this->addFieldResolverIfNotExists('User', 'mail',
      $this->builder->fromPath('entity', 'mail.value')
    );
  }

  protected function resolveMediaImageFields() {
    $this->resolveMediaInterfaceFields('MediaImage');
    $this->addFieldResolverIfNotExists('MediaImage', 'copyright',
      $this->builder->fromPath('entity', 'field_copyright.value')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'description',
      $this->builder->fromPath('entity', 'field_description.processed')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'src',
      $this->builder->compose(
        $this->builder->fromPath('entity', 'field_image.entity'),
        $this->builder->produce('image_url')
          ->map('entity', $this->builder->fromParent())
      )
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'derivative',
      $this->builder->compose(
        $this->builder->fromPath('entity', 'field_image.entity'),
        $this->builder->produce('image_derivative')
          ->map('entity', $this->builder->fromParent())
          ->map('style', $this->builder->fromArgument('style')),
        $this->builder->callback(function (array $values) {
          return $values + ['src' => $values['url']];
        })
      )
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'focalPoint',
      $this->builder->compose(
        $this->builder->fromPath('entity', 'field_image.entity'),
        $this->builder->produce('focal_point')
          ->map('file', $this->builder->fromParent())
      )
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'width',
      $this->builder->fromPath('entity', 'field_image.width')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'height',
      $this->builder->fromPath('entity', 'field_image.height')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'title',
      $this->builder->fromPath('entity', 'field_image.title')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'alt',
      $this->builder->fromPath('entity', 'field_image.alt')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'tags',
      $this->fromEntityReference('field_tags')
    );

    $this->addFieldResolverIfNotExists('MediaImage', 'source',
      $this->builder->fromPath('entity', 'field_source.value')
    );
  }

  protected function resolveMediaVideoFields() {
    $this->resolveMediaInterfaceFields('MediaVideo');

    $this->addFieldResolverIfNotExists('MediaVideo', 'src',
      $this->builder->fromPath('entity', 'field_media_video_embed_field.value')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'author',
      $this->builder->fromPath('entity', 'field_author.value')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'caption',
      $this->builder->fromPath('entity', 'field_caption.processed')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'copyright',
      $this->builder->fromPath('entity', 'field_copyright.value')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'description',
      $this->builder->fromPath('entity', 'field_description.processed')
    );

    $this->addFieldResolverIfNotExists('MediaVideo', 'source',
      $this->builder->fromPath('entity', 'field_source.value')
    );
  }

  /**
   * Add paragraph field resolvers.
   */
  protected function resolveParagraphFields() {
    // Text.
    $this->resolveBaseFields('ParagraphText', 'paragraph');

    $this->addFieldResolverIfNotExists('ParagraphText', 'text',
      $this->builder->fromPath('entity', 'field_text.processed')
    );

    // Image.
    $this->resolveBaseFields('ParagraphImage', 'paragraph');

    $this->addFieldResolverIfNotExists('ParagraphImage', 'image',
      $this->builder->fromPath('entity', 'field_image.entity')
    );

    // Twitter.
    $this->resolveBaseFields('ParagraphTwitter', 'paragraph');
    $embedEntityProducer = $this->referencedEntityProducer('field_media');

    $this->addFieldResolverIfNotExists('ParagraphTwitter', 'url',
      $this->builder->compose(
        $embedEntityProducer,
        $this->builder->fromPath('entity', 'field_url.value')
      )
    );

    // Instagram.
    $this->resolveBaseFields('ParagraphInstagram', 'paragraph');
    $embedEntityProducer = $this->referencedEntityProducer('field_media');

    $this->addFieldResolverIfNotExists('ParagraphInstagram', 'url',
      $this->builder->compose(
        $embedEntityProducer,
        $this->builder->fromPath('entity', 'field_url.value')
      )
    );

    // Pinterest.
    $this->resolveBaseFields('ParagraphPinterest', 'paragraph');
    $embedEntityProducer = $this->referencedEntityProducer('field_media');

    $this->addFieldResolverIfNotExists('ParagraphPinterest', 'url',
      $this->builder->compose(
        $embedEntityProducer,
        $this->builder->fromPath('entity', 'field_url.value')
      )
    );

    // Gallery.
    $this->resolveBaseFields('ParagraphGallery', 'paragraph');
    $mediaEntityProducer = $this->referencedEntityProducer('field_media');

    $this->addFieldResolverIfNotExists('ParagraphGallery', 'name',
      $this->builder->compose(
        $mediaEntityProducer,
        $this->builder->produce('entity_label')
          ->map('entity', $this->builder->fromParent())
      )
    );

    $this->addFieldResolverIfNotExists('ParagraphGallery', 'images',
      $this->builder->compose(
        $mediaEntityProducer,
        $this->fromEntityReference('field_media_images')
      )
    );

    // Link.
    $this->resolveBaseFields('ParagraphLink', 'paragraph');
    $this->addFieldResolverIfNotExists('ParagraphLink', 'links',
      $this->builder->fromPath('entity', 'field_link')
    );

    // Video.
    $this->resolveBaseFields('ParagraphVideo', 'paragraph');
    $this->addFieldResolverIfNotExists('ParagraphVideo', 'video',
      $this->builder->fromPath('entity', 'field_video.entity')
    );

    // Quote.
    $this->resolveBaseFields('ParagraphQuote', 'paragraph');

    $this->addFieldResolverIfNotExists('ParagraphQuote', 'text',
      $this->builder->fromPath('entity', 'field_text.processed')
    );

  }

  /**
   * Resolves page types.
   *
   * @param mixed $value
   *   The current value.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The resolve context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve information.
   *
   * @return string
   *   Response type.
   */
  protected function resolvePageTypes($value, ResolveContext $context, ResolveInfo $info): string {
    if ($value instanceof NodeInterface || $value instanceof TermInterface || $value instanceof UserInterface) {
      if ($value->bundle() === 'page') {
        return 'BasicPage';
      }
      return $this->mapBundleToSchemaName($value->bundle());
    }
  }

  /**
   * Resolves media types.
   *
   * @param mixed $value
   *   The current value.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The resolve context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve information.
   *
   * @return string
   *   Response type.
   */
  protected function resolveMediaTypes($value, ResolveContext $context, ResolveInfo $info): string {
    if ($value instanceof MediaInterface) {
      return 'Media' . $this->mapBundleToSchemaName($value->bundle());
    }
  }

  /**
   * Resolves page types.
   *
   * @param mixed $value
   *   The current value.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The resolve context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve information.
   *
   * @return string
   *   Response type.
   */
  protected function resolveParagraphTypes($value, ResolveContext $context, ResolveInfo $info): string {
    if ($value instanceof ParagraphInterface) {
      return 'Paragraph' . $this->mapBundleToSchemaName($value->bundle());
    }
  }

}
