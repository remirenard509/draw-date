<?php

namespace App\Models;

use \PDO;
use stdClass;

class UserModel extends SqlConnect {
    private $table = "users";
    public $authorized_fields_to_update = ['username','bio', 'avatar', 'password'];
    private string $passwordSalt;

    public function __construct() {
        parent::__construct();
        $this->passwordSalt = getenv('PASSWORD_SALT');
    }
    
    public function delete(int $id) {
      $req = $this->db->prepare("DELETE FROM $this->table WHERE id = :id");
      $req->execute(["id" => $id]);
      return new stdClass();
    }

    public function getById(int $id) {
      $req = $this->db->prepare("SELECT * FROM users WHERE id = :id");
      $req->execute(["id" => $id]);

      return $req->rowCount() > 0 ? $req->fetch(PDO::FETCH_ASSOC) : new stdClass();
    }

    public function getAll(?int $limit = null) {
      $query = "SELECT * FROM {$this->table}";
      
      if ($limit !== null) {
          $query .= " LIMIT :limit";
          $params = [':limit' => (int)$limit];
      } else {
          $params = [];
      }
      
      $req = $this->db->prepare($query);
      foreach ($params as $key => $value) {
          $req->bindValue($key, $value, PDO::PARAM_INT);
      }
      $req->execute();
      
      return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLast() {
      $req = $this->db->prepare("SELECT * FROM $this->table ORDER BY id DESC LIMIT 1");
      $req->execute();

      return $req->rowCount() > 0 ? $req->fetch(PDO::FETCH_ASSOC) : new stdClass();
    }

    public function update(array $data, int $id) {
      $request = "UPDATE $this->table SET ";
      $params = [];
      $fields = [];
  
      foreach ($data as $key => $value) {
          if (in_array($key, $this->authorized_fields_to_update)) {


              $fields[] = "$key = :$key";
              $params[":$key"] = $value;
          }
      }
      if (empty($fields)) {
        throw new \Exception("No valid fields to update");
      }
      if (isset($data["password"])) {
        $saltedPassword = $data["password"] . $this->passwordSalt;
        $hashedPassword = password_hash($saltedPassword, PASSWORD_BCRYPT);
        $fields[] = "password = :password";
        $params[":password"] = $hashedPassword;
      }
      
      $params[':id'] = $id;
      $query = $request . implode(", ", $fields) . " WHERE id = :id";
  
      $req = $this->db->prepare($query);
      return $req->execute($params);
    }

  public function saveDrawing($id, $draw_svg) {
    $query = "UPDATE $this->table SET draw_svg = :draw_svg WHERE id = :id";
    $stmt = $this->db->prepare($query);
    $stmt->execute([
        'draw_svg' => $draw_svg,
        'id' => $id
    ]);
  }
  public function saveDescription($id, $draw_description) {
    $query = "UPDATE $this->table SET draw_description = :draw_description WHERE id = :id";
    $stmt = $this->db->prepare($query);
    $stmt->execute([
        'draw_description' => $draw_description,
        'id' => $id
    ]);
  }
  public function getIdByEmail($email) {
    $query = "SELECT id FROM $this->table WHERE email = :email";
    $stmt = $this->db->prepare($query);
    $stmt->execute(['email' => $email]);
    return $stmt->fetchColumn();
  }
  public function getDraws() {
    $query = "SELECT id, draw_svg, draw_description FROM $this->table WHERE draw_svg IS NOT NULL";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  public function updateActivated(array $data, int $id) {
    try {
    $query = "UPDATE $this->table SET activated = :activated WHERE id = :id";
    $stmt = $this->db->prepare($query);
    
    return $stmt->execute([
        'activated' => (int) $data['activated'],
        'id' => $id
    ]);
  }
  catch (\Exception $e) {
    error_log('Erreur : ' . $e->getMessage());
    throw new \Exception("An unexpected error occurred.", 500);
  }
  }
}