<?php
namespace ContactManager\Core;

class AutoloadHandler {
    /**
     * @var string directory prefix for class auto-loader
     */
    private $searchPrefix;
    public function __construct() {
        $this->searchPrefix = PROJECTS_BASE_DIRECTORY . '/src/php/';
        spl_autoload_register(array($this, 'loadClass'));
    }
    /**
     * @param string $className
     */
    private function loadClass($className) {
        @include_once($this->searchPrefix . str_replace( '\\', '/', $className ) . '.php');
    }
}