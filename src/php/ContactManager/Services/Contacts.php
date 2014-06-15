<?php
/*
 * @copyright jsmolka 
 * @link https://github.com/onkelsmo/contactManaging
 */
namespace ContactManager\Services;

class Contacts {
	private $db;
	private $userId;
	
	public function __construct($_db, $_userId) {
		$this->db = $_db;
		$this->userId = $_userId;
	}
	private function getUserId() {
		return $this->userId;
	}

	public function listContacts($contactId = -1) {
		try {
			$stmt = $this->db->prepare("
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
				throw new SQLException($this->db->error, $this->db->errno);
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

			$result = Array("data" => $data);
			return $result;
		} catch (\Exception $e) {
			if ($stmt) {
				$stmt->close();
			}
			throw $e;
		}
	}
	
	public function save($bearbeitet = array(), $new = array()) {
		try {
			// Geänderte Einträge speichern
			$stmt = $this->db->prepare("
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
				throw new SQLException($this->db->error, $this->db->errno);
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
			$stmt = $this->db->prepare("
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
				throw new SQLException($this->db->error, $this->db->errno);
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
			
			// Neue Einträge einfügen
			$stmt = $this->db->prepare("
				INSERT INTO
					eintrag (knt_id, ett_id, etg_wert)
				SELECT
					?, ?, ?
				FROM
					kontakt k
				WHERE
					k.knt_id = ?
				AND
					k.bnz_id = ?
				AND NOT EXISTS (
					SELECT
						*
					FROM
						eintrag e
					INNER JOIN
						eintragstyp t
					ON
						e.ett_id = t.ett_id
					WHERE
						e.knt_id = ?
					AND
						e.ett_id = ?
					AND 
						t.ett_eindeutig)
				");
			if(!$stmt) {
				throw new SQLException($this->db->error, $this->db->errno);
			}
			$stmt->bind_param(
					"iisiiii",
					$contactId,
					$insertTypeId,
					$value,
					$contactId,
					$userId,
					$contactId,
					$insertTypeId);
			$inserted = array();
			foreach($new as $contactId => $inserts) {
				foreach($inserts as $insertTypeId => $values) {
					foreach($values as $valueId => $value) {
						if(trim($value)) {
							$stmt->execute();
							if($stmt->affected_rows > 0) {
								$inserted[$contactId][$insertTypeId][$valueId] = $stmt->insert_id;
							}
						}
					}
				}
			}
			$stmt->close();
			
			return $inserted;
		} catch (\Exception $e) {
			if($stmt) {
				$stmt->close();
			}
			throw $e;
		}
	}
}
