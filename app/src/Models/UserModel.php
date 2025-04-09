<?php

namespace App\Models;

use \PDO;
use stdClass;

class UserModel extends SqlConnect {
    private $table = "users";
    public $authorized_fields_to_update = ['firstname', 'name', 'address', 'phone', 'password'];
    private string $passwordSalt = "sqidq7sà";

    public function delete(int $id) {
      $req = $this->db->prepare("DELETE FROM $this->table WHERE id = :id");
      $req->execute(["id" => $id]);
      return new stdClass();
    }

    public function get(int $id) {
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
  
      # Prepare the query dynamically based on the provided data
      foreach ($data as $key => $value) {
          if (in_array($key, $this->authorized_fields_to_update)) {


              $fields[] = "$key = :$key";
              $params[":$key"] = $value;
          }
      }
      if (isset($data["password"])) {
        // Combine password with salt and hash it
        $saltedPassword = $data["password"] . $this->passwordSalt;
        $hashedPassword = password_hash($saltedPassword, PASSWORD_BCRYPT);
    
        // Ajouter le password hashé aux paramètres et aux champs
        $fields[] = "password = :password";
        $params[":password"] = $hashedPassword;
      }
      
      $params[':id'] = $id;
      $query = $request . implode(", ", $fields) . " WHERE id = :id";
  
      $req = $this->db->prepare($query);
      $req->execute($params);
      
      return $this->get($id);
    }

  public function saveDrawing($id, $draw_svg) {
    $query = "UPDATE $this->table SET draw_svg = :draw_svg WHERE id = :id";
    $stmt = $this->db->prepare($query);
    $stmt->execute([
        'draw_svg' => $draw_svg,
        'id' => $id
    ]);
  }
}