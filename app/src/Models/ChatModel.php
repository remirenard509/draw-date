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
  public function getLastestMessage($id) {
    try {
        $query = "SELECT m.*, u.username 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.receiver_id = :id AND m.readByReceiver = 0
        LIMIT 1;
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
         'id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch  (\PDOException $e) {
        error_log('Erreur SQL : ' . $e->getMessage());
        throw new \Exception("Failed to check messages");
    }
  }

 public function getChat($senderId, $receiverId) {
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
            (messages.sender_id = :senderId AND messages.receiver_id = :receiverId)
            OR
            (messages.sender_id = :receiverId AND messages.receiver_id = :senderId)
        ORDER BY 
            messages.id DESC
        LIMIT 20;";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'senderId' => $senderId,
            'receiverId' => $receiverId
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

    public function setMessageAsRead($id, $sender_id) {
        try {
           $query = "UPDATE $this->table SET readByReceiver = :readByReceiver WHERE receiver_id = :id and sender_id = :sender_id";
           $stmt = $this->db->prepare($query);
           return $stmt->execute([
             'readByReceiver' => '1',
             'id' => $id,
             'sender_id' => $sender_id
           ]);
        } catch  (\PDOException $e) {
          error_log('Erreur SQL : ' . $e->getMessage());
          throw new \Exception("Failed to set messages as read.");
        } 
    }
  

    public function fetchprofil($id) {
        $query = "SELECT username, bio, avatar FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'id' => $id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}