<?php

namespace Drupal\thunder_gqls\Plugin\GraphQL\DataProducer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Drupal\focal_point\FocalPointManagerInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resolves the focal point positions for a file.
 *
 * @DataProducer(
 *   id = "paragraph_summary",
 *   name = @Translation("FocalPoint"),
 *   description = @Translation("Resolves the focal point positions for a file."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Focal point positions")
 *   ),
 *   consumes = {
 *     "paragraph" = @ContextDefinition("entity",
 *       label = @Translation("Root value")
 *     )
 *   }
 * )
 */
class ParagraphSummary extends DataProducerPluginBase {

  /**
   * Resolve the focal point positions.
   *
   * @param \Drupal\file\FileInterface $file
   *   The entity.
   *
   * @return mixed
   *   The focal point position tag.
   */
  public function resolve(Paragraph $paragraph) {
    return $paragraph->getSummaryItems();
  }

}
