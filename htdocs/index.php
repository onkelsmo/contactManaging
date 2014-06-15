<?php
/* 
 * @copyright jsmolka
 * @link https://github.com/onkelsmo/contactManaging
 */
ini_set('display_errors', 1);

include_once '../bootstrap.php';
include_once './templates/contacts.html';

$dispatcher = new ContactManager\Services\Dispatcher();
$dispatcher->handleHttpRequest();
