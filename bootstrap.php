<?php

define('PROJECTS_BASE_DIRECTORY', realpath(__DIR__));

require_once(PROJECTS_BASE_DIRECTORY . '/src/php/ContactManager/Core/AutoloadHandler.php');

new \ContactManager\Core\AutoloadHandler();
//new \Freya\Core\ErrorHandler();
