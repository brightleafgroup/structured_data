<?php

namespace Drupal\structured_data\Core;

class Helper {
	
	public static function getPageJson($route_name, $url = NULL) {
		$query = db_select('structured_data_json', 'sdj')
					->fields('sdj')
					->condition('route_name', $route_name);
					
		if(empty($url)) {
			$query
				->addExpression("TRIM(IFNULL(url, '')) = ''");
		} else {
			$query
				->condition('url', $url);
		}
		
		$result = $query
				->execute()
				->fetchObject();
		
		return ($result);
	}

	public static function updatePageJson(&$entity) {
		$existing_obj = self::getPageJson($entity['route_name'], $entity['url']);
		
		if($existing_obj == NULL) {
			$entity['id'] = db_insert('structured_data_json')
				->fields($entity)
				->execute();
		} else {
			db_update('structured_data_json')
				->fields($entity)
				->condition('id', $existing_obj->id)
				->execute();
		}
	}
}
