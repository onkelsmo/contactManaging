<?php
/*
 * @copyright jsmolka 
 * @link https://github.com/onkelsmo/contactManaging
 */
namespace ContactManager\Services;

class Dispatcher {
	private $db;
	
	public function __construct() {
		$this->db = new \mysqli('localhost', 'root', 'hedpe1981', 'buch');
		$this->db->set_charset('utf8');
	}
	public function __destruct() {
		if($this->db) {
			$this->db->close();
		}
	}
	
	public function handleHttpRequest() {
		$serviceName = $_GET['$service'];
		$operationName = $_GET['$operation'];
		$service = self::initService($serviceName);
		
		$result = self::methodCall($service, $operationName);
		if($result !== null) {
			header('Content-type: text/plain; charset=UTF-8');
			echo json_encode($result);
		}
	}
	private function initService($serviceName) {
		require_once ("$serviceName.php");
		$refService = new \ReflectionClass($serviceName);
		$refConstructor = $refService->getConstructor();
		if(!$refConstructor) {
			return $refService->newInstance();
		}
		$initParameter = self::setParams($refConstructor);
		return $refService->newInstanceArgs($initParameter);
	}
	private function setParams($operation) {
		$callParam = Array();
		$refParam = $operation->getParameters();
		foreach($refParam as $p => $param) {
			$paramName = $param->getName();
			if($paramName == '_db') {
				$value = $this->db;
			} elseif($paramName[0] != '_'
					&& $_SERVER['REQUEST_METHOD'] == 'POST'
					&& array_key_exists($paramName, $_POST)) {
				$value = $_POST[$paramName];
			} elseif($paramName[0] != '_'
					&& array_key_exists($paramName, $_GET)) {
				$value = $_GET[$paramName];
			} elseif($param->isDefaultValueAvailable()) {
				$value = $param->getDefaultValue();
			} else {
				$value = null;
			}
			$callParam[$p] = $value;
		}
		return $callParam;
	}
	
	private static function methodCall($service, $operation) {
		
	}
}

