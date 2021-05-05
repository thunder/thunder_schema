<?php

namespace Drupal\thunder_gqls\Wrappers;

use Drupal\Core\Entity\Query\QueryInterface;
use GraphQL\Deferred;

/**
 * The thunder image response class.
 */
class ImageResponse {

  /**
   * @var array
   */
  private $imageData;

  /**
   * EntityListResponse constructor.
   *
   * @param array $imageData
   *   The image data.
   */
  public function __construct(array $imageData) {
    $this->imageData = $imageData;
  }

  /**
   * The image source URL.
   *
   * @return string
   *   The image src.
   */
  public function src(): string {
    return $this->imageData['src'];
  }

  /**
   * The image width.
   *
   * @return int
   *   The image width.
   */
  public function width(): int {
    return $this->imageData['width'];
  }

  /**
   * The image height.
   *
   * @return int
   *   The image height.
   */
  public function height(): int {
    return $this->imageData['height'];
  }

}

