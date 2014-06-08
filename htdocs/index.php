<?php
/* 
 * @copyright jsmolka
 * @link https://github.com/onkelsmo/contactManaging
 */
ini_set('display_errors', 1);

include_once '../bootstrap.php';

use ContactManager\Services\Contacts;

$contacts = new Contacts();

header('Content-type: text/plain; charset=UTF-8');
if($_SERVER['REQUEST_METHOD'] == 'POST' &&
		array_key_exists('contactId', $_POST)) {
	$contactId = $_POST['contactId'];
} elseif(array_key_exists('contactId', $_GET)) {
	$contactId = $_GET['contactId'];
} else {
	$contactId = -1;
}

echo json_encode($contacts->listContacts($contactId));

