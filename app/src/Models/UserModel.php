<?php

namespace App\Models;

use \PDO;
use stdClass;

class UserModel extends SqlConnect {
    private string $table = "users";
    public array $authorized_fields_to_update = ['username', 'bio', 'avatar', 'password', 'activated'];
    private string $passwordSalt;

    public function __construct() {
        parent::__construct();
        $this->passwordSalt = getenv('PASSWORD_SALT');
    }

    public function delete(int $id): bool {
        try {
            $req = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $req->execute(['id' => $id]);
            return $req->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to delete user.");
        }
    }

    public function getById(int $id): array|stdClass {
        try {
            $req = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $req->execute(['id' => $id]);
            return $req->rowCount() > 0 ? $req->fetch(PDO::FETCH_ASSOC) : new stdClass();
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to retrieve user.");
        }
    }

    public function getAll(): array {
        try {
            $query = "SELECT id, email, username, gender, search_gender, dob, bio, avatar, draw_svg, draw_description, activated, superMatch FROM {$this->table}";
            $req = $this->db->prepare($query);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to retrieve users.");
        }
    }

    public function update(array $data, int $id): bool {
        try {
            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                if (in_array($key, $this->authorized_fields_to_update)) {
                    if ($key === 'password') {
                        $value = password_hash($value . $this->passwordSalt, PASSWORD_BCRYPT);
                    }
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            if (empty($fields)) {
                throw new \Exception("No valid fields to update.");
            }

            $query = "UPDATE {$this->table} SET " . implode(", ", $fields) . " WHERE id = :id";
            $req = $this->db->prepare($query);
            return $req->execute($params);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to update user.");
        }
    }

    public function saveDrawing(int $id, string $draw_svg): void {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET draw_svg = :draw_svg WHERE id = :id");
            $stmt->execute(['draw_svg' => $draw_svg, 'id' => $id]);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to save drawing.");
        }
    }

    public function saveDescription(int $id, string $draw_description): void {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET draw_description = :draw_description WHERE id = :id");
            $stmt->execute(['draw_description' => $draw_description, 'id' => $id]);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to save description.");
        }
    }

    public function getIdByEmail(string $email): int|false {
        try {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to retrieve user ID by email.");
        }
    }

    public function getNumberOfSuperMatch(int $id): int {
        try {
            $stmt = $this->db->prepare("SELECT superMatch FROM {$this->table} WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['superMatch'] ?? 0;
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to get number.");
        }
    }

    public function getDraws(int $id): array {
        try {
            $query = "
                SELECT id, draw_svg, draw_description
                FROM {$this->table}
                WHERE draw_svg IS NOT NULL
                AND id != :id
                AND id NOT IN (
                    SELECT user2_id FROM `match` WHERE user1_id = :id
                    UNION
                    SELECT user1_id FROM `match` WHERE user2_id = :id
                )
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to retrieve drawings.");
        }
    }

    public function updateActivated(array $data, int $id): bool {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET activated = :activated WHERE id = :id");
            return $stmt->execute([
                'activated' => (int)$data['activated'],
                'id' => $id
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to update activation status.");
        }
    }

    public function getUsername(): array {
        try {
            $stmt = $this->db->prepare("SELECT id, username FROM {$this->table}");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to retrieve usernames.");
        }
    }

    public function setSuperMatch(int $id, array $data): bool {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET superMatch = :value WHERE id = :id");
            return $stmt->execute([
                'value' => $data['superMatch'],
                'id' => $id
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to update superMatch.");
        }
    }

    public function getIdFromEmail(array $data): array|false {
        try {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = :email");
            $stmt->execute(['email' => $data['email']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to retrieve ID from email.");
        }
    }

    public function saveLocation($id, $data) {
        try {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET latitude = :latitude, longitude = :longitude WHERE id = :id");
            return $stmt->execute([
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'id' => $id
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to update location.");
        }
    }
}
