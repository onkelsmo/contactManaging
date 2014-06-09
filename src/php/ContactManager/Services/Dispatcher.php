<?php
/*
 * @copyright jsmolka 
 * @link https://github.com/onkelsmo/contactManaging
 */
namespace ContactManager\Services;

class Dispatcher {
	public static function handleHttpRequest() {
		$serviceName = $_GET['$service'];
		$operationName = $_GET['$operation'];
		$service = self::initService($serviceName);
		
		$result = self::methodCall($service, $operationName);
		if($result !== null) {
			header('Content-type: text/plain; charset=UTF-8');
			echo json_encode($result);
		}
	}
	private static function initService($serviceName) {
		require_once ("$serviceName.php");
		$refService = new \ReflectionClass($serviceName);
		return $refService->newInstance();
	}
	private static function methodCall($service, $operation) {
		
	}
}

