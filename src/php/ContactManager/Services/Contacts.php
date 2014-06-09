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
	
	public function save($bearbeitet = array(), $neu = array()) {
		$db = self::db();
		try {
			// Geänderte Einträge speichern
			$stmt = $db->prepare("
					UPDATE
						eintrag e
					INNER JOIN
						kontakt k
					ON
						k.knt_id = e.knt_id
					SET
						e.etg_wert = ?
					WHERE
						etg_id = ? 
					AND
						k.bnz_id = ?
					");
			if(!$stmt) {
				throw new SQLException($db->error, $db->errno);
			}
			$userId = $this->getUserId();
			$stmt->bind_param(
					"sii",
					$value,
					$insertId,
					$userId);
			foreach($bearbeitet as $insertId => $value) {
				if(trim($value)) {
					$stmt->execute();
				}
			}
			$stmt->close();
			
			// leere Einträge löschen
			$stmt = $db->prepare("
					DELETE FROM
						eintrag e
					USING
						eintrag e
					INNER JOIN
						kontakt k
					ON
						e.knt_id = k.knt_id
					WHERE
						e.etg_id = ?
					AND
						k.bnz_id = ?
					");
			if(!$stmt) {
				throw new SQLException($db->error, $db->errno);
			}
			$userId = $this->getUserId();
			$stmt->bind_param(
					"ii",
					$insertId,
					$userId);
			foreach ($bearbeitet as $insertId => $value) {
				if(!trim($value)) {
					$stmt->execute();
				}
			}
			$stmt->close();
		} catch (Exception $ex) {

		}
	}

}
