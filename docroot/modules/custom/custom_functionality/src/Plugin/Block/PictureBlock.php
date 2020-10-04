<?php

namespace Drupal\custom_functionality\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;

/**
 * Provides a 'Picture' Block.
 *
 * @Block(
 *   id = "picture_block",
 *   admin_label = @Translation("Picture block"),
 *   category = @Translation("Pictures"),
 * )
 */
class PictureBlock extends BlockBase {

  public function build() {
    $entity_manager = \Drupal::entityManager()->getStorage('file');
    $query = $entity_manager->getQuery();
    $query->condition('uri', 'public://2020-09/il_570xN.1300551301_81un.jpg');
    $fids = $query->execute();

    if (count($fids) && $file = File::load(reset($fids))) {
      $variables = array(
        'style_name' => 'thumbnail',
        'uri' => $file->getFileUri(),
      );

      // The image.factory service will check if our image is valid.
      $image = \Drupal::service('image.factory')->get($file->getFileUri());
      if ($image->isValid()) {
        $variables['width'] = $image->getWidth();
        $variables['height'] = $image->getHeight();
      }
      else {
        $variables['width'] = $variables['height'] = NULL;
      }

      $picture_build = [
        '#theme' => 'image_style',
        '#width' => $variables['width'],
        '#height' => $variables['height'],
        '#style_name' => $variables['style_name'],
        '#uri' => $variables['uri'],
      ];

      // Add the file entity to the cache dependencies.
      // This will clear our cache when this entity updates.
      $renderer = \Drupal::service('renderer');
      $renderer->addCacheableDependency($picture_build, $file);

      // Return the render array as block content.
      return [
        'picture' => $picture_build,
      ];
    } else {
      // Image not found, return empty block.
      return [];
    }
  }
}
