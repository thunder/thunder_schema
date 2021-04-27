<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns if menu tree element is in active trail.
 *
 * @todo Fix input and output context type.
 *
 * @DataProducer(
 *   id = "menu_tree_in_active_trail",
 *   name = @Translation("Menu tree element is in active trail"),
 *   description = @Translation("Returns if the menu tree element is in active trail."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Menu link tree is in active trail.")
 *   ),
 *   consumes = {
 *     "element" = @ContextDefinition("any",
 *       label = @Translation("Menu link tree element")
 *     )
 *   }
 * )
 */
class MenuTreeInActiveTrail extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement $element
   *   The menu link tree element.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface
   */
  public function resolve(MenuLinkTreeElement $element) {
    return $element->inActiveTrail;
  }

}
