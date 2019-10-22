<?php

namespace Drupal\structured_data\Core;

use Drupal\Core\Url;

/**
 * Class Helper.
 *
 * @package Drupal\structured_data\Core
 */
class Helper {

  public const EMPTY_BUNDLE = 'none';

  /**
   * Get current page meta data.
   *
   * @param bool $fillEmptyValues
   *   Defaults to FALSE.
   *
   * @return array
   *   An array of meta data.
   */
  public static function getCurrentPageMeta($fillEmptyValues = FALSE) {
    $route = \Drupal::routeMatch();
    $route_name = $route->getRouteName();

    $url = Url::fromRoute('<current>');
    $urlString = $url->toString();

    $matches = [];
    $result = preg_match("/entity\\.([a-zA-Z0-9_]+)\\.canonical/", $route_name, $matches);
    if ($result == 1) {
      $bundle = $matches[1];
      $entity_id = $route->getRawParameter($bundle);
    }
    else {
      $bundle = $fillEmptyValues ? self::EMPTY_BUNDLE : '';
      $entity_id = $fillEmptyValues ? '0' : '';
    }

    $meta = [
      'route_name' => $route_name,
      'url' => $urlString,
      'bundle' => $bundle,
      'entity_id' => $entity_id,
    ];

    return $meta;
  }

  /**
   * Get page JSON for route.
   *
   * @param string $route_name
   *   Route name string.
   * @param string $url
   *   Optional URL.
   *
   * @return mixed
   *   JSON for route.
   */
  public static function getPageJsonForRoute($route_name, $url = NULL) {
    $query = \Drupal::database()->select('structured_data_json', 'sdj')
      ->fields('sdj')
      ->condition('route_name', $route_name);

    if (empty($url)) {
      $query
        ->addExpression("TRIM(IFNULL(url, '')) = ''");
    }
    else {
      $query
        ->condition('url', $url);
    }

    $result = $query
      ->execute()
      ->fetchObject();

    return ($result);
  }

  /**
   * Get page JSON for entity.
   *
   * @param string $bundle
   *   Bundle.
   * @param int $entity_id
   *   Entity id.
   *
   * @return mixed
   *   JSON for entity..
   */
  public static function getPageJsonForEntity($bundle, $entity_id) {
    $query = \Drupal::database()->select('structured_data_json', 'sdj')
      ->fields('sdj')
      ->condition('bundle', $bundle)
      ->condition('entity_id', $entity_id);

    $result = $query
      ->execute()
      ->fetchObject();

    return ($result);
  }

  /**
   * Get page JSON.
   *
   * @param array $params
   *   An array of parameters.
   *
   * @return mixed
   *   Page JSON.
   */
  public static function getPageJson(array $params) {
    $obj = (empty($params['entity_id']) || $params['entity_id'] == '0') ? self::getPageJsonForRoute($params['route_name'], $params['url']) : self::getPageJsonForEntity($params['bundle'], $params['entity_id']);
    return ($obj);
  }

  /**
   * Update Page JSON.
   *
   * @param array $entity
   *   Entity.
   *
   * @throws \Exception
   */
  public static function updatePageJson(array &$entity) {
    $existing_obj = self::getPageJson($entity);

    if (empty($entity['entity_id'])) {
      unset($entity['bundle']);
      unset($entity['entity_id']);
    }

    if ($existing_obj == NULL) {
      $entity['id'] = \Drupal::database()->insert('structured_data_json')
        ->fields($entity)
        ->execute();
    }
    else {
      \Drupal::database()->update('structured_data_json')
        ->fields($entity)
        ->condition('id', $existing_obj->id)
        ->execute();
    }
  }

}
