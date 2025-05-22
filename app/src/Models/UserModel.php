<?php

namespace App\Models;

use \PDO;
use stdClass;

class UserModel extends SqlConnect {
  private $table = "users";
  public $authorized_fields_to_update = ['username', 'bio', 'avatar', 'password'];
  private string $passwordSalt;

  public function __construct() {
      parent::__construct();
      $this->passwordSalt = getenv('PASSWORD_SALT');
  }

  public function delete(int $id) {
      try {
          $req = $this->db->prepare("
          DELETE FROM $this->table WHERE id = :id");
          $req->execute(["id" => $id]);
          return $req->rowCount() > 0;
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to delete user.");
      }
  }

  public function getById(int $id) {
      try {
          $req = $this->db->prepare("SELECT * FROM $this->table WHERE id = :id");
          $req->execute(["id" => $id]);
          return $req->rowCount() > 0 ? $req->fetch(PDO::FETCH_ASSOC) : new stdClass();
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to retrieve user.");
      }
  }

  public function getAll(?int $limit = null) {
      try {
          $query = "SELECT * FROM $this->table";
          if ($limit !== null) {
              $query .= " LIMIT :limit";
          }
          $req = $this->db->prepare($query);
          if ($limit !== null) {
              $req->bindValue(':limit', $limit, PDO::PARAM_INT);
          }
          $req->execute();
          return $req->fetchAll(PDO::FETCH_ASSOC);
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to retrieve users.");
      }
  }

  public function update(array $data, int $id) {
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

          $query = "UPDATE $this->table SET " . implode(", ", $fields) . " WHERE id = :id";
          $req = $this->db->prepare($query);
          return $req->execute($params);
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to update user.");
      }
  }

  public function saveDrawing($id, $draw_svg) {
      try {
          $query = "UPDATE $this->table SET draw_svg = :draw_svg WHERE id = :id";
          $stmt = $this->db->prepare($query);
          $stmt->execute([
              'draw_svg' => $draw_svg,
              'id' => $id
          ]);
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to save drawing.");
      }
  }

  public function saveDescription($id, $draw_description) {
      try {
          $query = "UPDATE $this->table SET draw_description = :draw_description WHERE id = :id";
          $stmt = $this->db->prepare($query);
          $stmt->execute([
              'draw_description' => $draw_description,
              'id' => $id
          ]);
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to save description.");
      }
  }

  public function getIdByEmail($email) {
      try {
          $query = "SELECT id FROM $this->table WHERE email = :email";
          $stmt = $this->db->prepare($query);
          $stmt->execute(['email' => $email]);
          return $stmt->fetchColumn();
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to retrieve user ID by email.");
      }
  }

  public function getDraws() {
      try {
          $query = "SELECT id, draw_svg, draw_description FROM $this->table WHERE draw_svg IS NOT NULL";
          $stmt = $this->db->prepare($query);
          $stmt->execute();
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to retrieve drawings.");
      }
  }

  public function updateActivated(array $data, int $id) {
      try {
          $query = "UPDATE $this->table SET activated = :activated WHERE id = :id";
          $stmt = $this->db->prepare($query);
          return $stmt->execute([
              'activated' => (int) $data['activated'],
              'id' => $id
          ]);
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to update activation status.");
      }
  }

  public function getUsername() {
      try {
          $query = "SELECT id, username FROM $this->table";
          $stmt = $this->db->prepare($query);
          $stmt->execute();
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to retrieve usernames.");
      }
  }

  

}