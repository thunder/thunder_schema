<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\Schema;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\thunder_gqls\Traits\ResolverHelperTrait;

/**
 * Tha base schema for Thunder composable schema.
 *
 * @Schema(
 *   id = "thunder",
 *   name = "Thunder composable schema"
 * )
 */
class ThunderSchema extends ComposableSchema {

  use ResolverHelperTrait;

  const REQUIRED_EXTENSIONS = [
    'thunder_pages',
    'thunder_media',
    'thunder_paragraphs',
  ];

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    $this->registry = new ResolverRegistry();
    $this->createResolverBuilder();

    $this->resolveBaseTypes();

    return $this->registry;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExtensions() {
    return array_map(function ($id) {
      return $this->extensionManager->createInstance($id);
    }, array_unique(array_filter($this->getConfiguration()['extensions']) + static::REQUIRED_EXTENSIONS));
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $extensions = $this->extensionManager->getDefinitions();

    foreach ($extensions as $key => $extension) {
      if (in_array($extension['id'], static::REQUIRED_EXTENSIONS)) {
        $form['extensions'][$key]['#disabled'] = TRUE;
        $form['extensions'][$key]['#default_value'] = TRUE;
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    return SdlSchemaPluginBase::getSchemaDefinition();
  }

  /**
   * Resolve custom types, that are used in multiple places.
   */
  private function resolveBaseTypes() {
    $this->addFieldResolverIfNotExists('Link', 'url',
      $this->builder->callback(function ($parent) {
        if (!empty($parent) && isset($parent['uri'])) {
          $urlObject = Url::fromUri($parent['uri']);
          $url = $urlObject->toString(TRUE)->getGeneratedUrl();
        }
        return $url ?? '';
      })
    );

    $this->addFieldResolverIfNotExists('Link', 'title',
      $this->builder->callback(function ($parent) {
        return $parent['title'];
      })
    );
  }

}
