<?php
/*
 * @copyright jsmolka 
 * @link https://github.com/onkelsmo/contactManaging
 */
namespace ContactManager\Services;

class Security {
	private $db;
	
	public function __construct($_db) {
		$this->db = $_db;
	}
	
	public function logout() {
		unset($_SESSION['user']);
		return true;
	}
	public function login($userName, $password) {
		if(!trim($userName) || !trim($password)) {
			return false;
		}
		$passwordHash = md5("Adresbuch.$userName.$password");
		$userId = null;
		
		try {
			$stmt = $this->db->prepare("
				SELECT 
					bnz_id,bnz_kennworthash
				FROM
					benutzer
				WHERE
					bnz_benutzername = ?
				");
			
			if(!$stmt) {
				throw new SQLException(
						$this->db->error,
						$this->db->errno);
			}
			$stmt->bind_param("s", $userName);
			$stmt->bind_result($userId, $hash);
			$stmt->execute();
			
			if($stmt->fetch()) {
				if($hash != $passwordHash) {
					$userId = null;
				}
			} else {
				$stmt->close();
				$stmt = $this->db->prepare("
						INSERT INTO
							benutzer (bnz_benutzername, bnz_kennworthash)
						VALUES
							(?, ?)
						");
				if(!$stmt) {
					throw new SQLException($this->db->error, $this->db->errno);
				}
				$stmt->bind_param("ss", $userName, $passwordHash);
				if(!$stmt->execute()) {
					throw new SQLException($this->db->error, $this->db->errno);
				}
				if($stmt->affected_rows > 0) {
					$userId = $stmt->insert_id;
				}
			}
			$stmt->close();
			
			if($userId) {
				$_SESSION['user'] = array(
					'id' => $userId,
					'name' => $userName);
				return true;
			} else {
				unset($_SESSION['user']);
				return false;
			}
			
		} catch (Exception $e) {
			if($stmt) {
				$stmt->close();
				throw $e;
			}
		}
	}
}

