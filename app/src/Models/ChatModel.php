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
    $query = "SELECT users.username, messages.content
    FROM $this->table
    JOIN users ON messages.sender_id = users.id
    WHERE messages.receiver_id = :id;";
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