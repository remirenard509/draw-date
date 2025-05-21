<?php

namespace App\Models;

use \PDO;
use stdClass;

class ChatModel extends SqlConnect {
  private $table = "messages";
  
  public function __construct() {
    parent::__construct();
  }
  public function sendMessage($senderId, $receiverId, $content) {
    try{
    $query = "INSERT INTO $this->table ( `sender_id`, `receiver_id`, `content`) VALUES (:sender_id, :receiver_id, :content)";
    $stmt = $this->db->prepare($query);
    $stmt->execute([
        'sender_id' => $senderId,
        'receiver_id' => $receiverId,
        'content' => $content
    ]);
    } catch  (\PDOException $e) {
        error_log('Erreur SQL : ' . $e->getMessage());
        throw new \Exception("Failed to send message.");
    }
  }

  public function getChatFromId($id) {
    try {
    $query = "SELECT 
    users.username, 
    messages.content,
    messages.sender_id,
    messages.receiver_id
FROM 
    messages
JOIN 
    users ON messages.sender_id = users.id
WHERE 
    messages.receiver_id = :id OR messages.sender_id = :id
ORDER BY 
    messages.id ASC;";
    $stmt = $this->db->prepare($query);
    $stmt->execute([
        'id' => $id
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch  (\PDOException $e) {
        error_log('Erreur SQL : ' . $e->getMessage());
        throw new \Exception("Failed to get message.");
    }
  }

  public function match($user1_id, $user2_id) {
    try {
      $query = "INSERT INTO `match`(`user1_id`, `user2_id`) VALUES (:user1_id, :user2_id)";
      $stmt = $this->db->prepare($query);
      $stmt->execute([
          'user1_id' => $user1_id,
          'user2_id' => $user2_id
      ]);
      } catch  (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to match.");
      }
    }
    public function getMatch($id) {
      try {
      $query = "SELECT u.id, u.username
      FROM `match` m
      JOIN users u ON u.id = 
          CASE 
              WHEN m.user1_id = :id THEN m.user2_id
              ELSE m.user1_id
          END
      WHERE m.user1_id = :id OR m.user2_id = :id;
      ";
      $stmt = $this->db->prepare($query);
      $stmt->execute([
          'id' => $id
      ]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch  (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to get message.");
      }
    }
  
}