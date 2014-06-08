<?php
/*
 * @copyright jsmolka 
 * @link https://github.com/onkelsmo/contactManaging
 */
namespace ContactManager\Services;

class Contacts {
	private static function db() {
		$db = new \mysqli('localhost', 'root', 'hedpe1981', 'buch');
		$db->set_charset("utf8");
		return $db;
	}

	private function getUserId() {
		return 1;
	}

	public function listContacts($contactId = -1) {
		$db = self::db();
		try {
			$stmt = $db->prepare("
                    SELECT 
                        k.knt_id, 
                        t.ett_id, 
                        t.ett_name, 
                        t.ett_eindeutig, 
                        e.etg_id, 
                        e.etg_wert 
                    FROM
                        kontakt k
                    INNER JOIN
                        eintrag e
                    ON
                        k.knt_id = e.knt_id
                    INNER JOIN
                        eintragstyp t
                    ON 
                        t.ett_id = e.ett_id
                    WHERE
                        k.bnz_id = ?
                    AND
                        (k.knt_id = ? or ? = -1)
                    ORDER BY
                        k.knt_id, 
                        t.ett_reihenfolge,
                        t.ett_name,
                        e.etg_id
                    ");
			if (!$stmt) {
				throw new SQLException($db->error, $db->errno);
			}

			$userId = $this->getUserId();

			$stmt->bind_param("iii", $userId, $contactId, $contactId);
			$stmt->bind_result(
					$contactId, $typeId, $typeName, $unique, $valueId, $value);
			$stmt->execute();

			$data = array();
			$lastContact = -1;
			$lastTypeId = null;

			while ($stmt->fetch()) {
				if ($contactId != $lastContact) {
					$lastContact = $contactId;
					$insert = -1;
				}
				if ($lastTypeId != $typeId) {
					++$insert;
					$data[$contactId][$insert]->name = $lastTypeName = $typeName;
					$data[$contactId][$insert]->id = $lastTypeId = $typeId;
				}
				if ($unique) {
					$data[$contactId][$insert]->insert = Array(
						'id' => $valueId,
						'wert' => $value);
				} else {
					$data[$contactId][$insert]->insert[] = Array(
						'id' => $valueId,
						'wert' => $value);
				}
			}

			$stmt->close();
			$db->close();

			$result = Array("data" => $data);
			return $result;
		} catch (\Exception $e) {
			if ($stmt) {
				$stmt->close();
			}
			if ($db) {
				$db->close();
			}
			throw $e;
		}
	}

}
