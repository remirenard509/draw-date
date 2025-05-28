<?php

namespace App\Models;

use \PDO;
use stdClass;

class ChatModel extends SqlConnect {
    private string $table = "messages";
    private string $matchTable = "match";

    public function __construct() {
        parent::__construct();
    }

    public function sendMessage(int $senderId, int $receiverId, string $content): void {
        try {
            $query = "INSERT INTO {$this->table} (sender_id, receiver_id, content) VALUES (:sender_id, :receiver_id, :content)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'content' => $content
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to send message.");
        }
    }

    public function getLastestMessage(int $id): array {
        try {
            $query = "SELECT m.*, u.username
                      FROM {$this->table} m
                      JOIN users u ON m.sender_id = u.id
                      WHERE m.receiver_id = :id AND m.readByReceiver = 0
                      LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to check messages.");
        }
    }

    public function getChat(int $senderId, int $receiverId): array {
        try {
            $query = "SELECT u.username, m.content, m.sender_id, m.receiver_id
                      FROM {$this->table} m
                      JOIN users u ON m.sender_id = u.id
                      WHERE (m.sender_id = :senderId AND m.receiver_id = :receiverId)
                         OR (m.sender_id = :receiverId AND m.receiver_id = :senderId)
                      ORDER BY m.id DESC
                      LIMIT 20";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'senderId' => $senderId,
                'receiverId' => $receiverId
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to get message.");
        }
    }

    public function match(int $user1_id, int $user2_id): void {
        try {
            $query = "INSERT INTO `{$this->matchTable}` (user1_id, user2_id) VALUES (:user1_id, :user2_id)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user1_id' => $user1_id,
                'user2_id' => $user2_id
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to match.");
        }
    }

    public function getMatch(int $id): array {
        try {
            $query = "SELECT u.id, u.username
            FROM `{$this->matchTable}` m
            JOIN users u ON u.id = CASE 
                WHEN m.user1_id = :id THEN m.user2_id
                ELSE m.user1_id
            END
            WHERE m.user1_id = :id OR m.user2_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to get match.");
        }
    }

    public function setMessageAsRead(int $receiverId, int $senderId): bool {
        try {
            $query = "UPDATE {$this->table} SET readByReceiver = 1 WHERE receiver_id = :receiver_id AND sender_id = :sender_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'receiver_id' => $receiverId,
                'sender_id' => $senderId
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to set messages as read.");
        }
    }

    public function fetchprofil(int $id): array {
        try {
            $query = "SELECT username, bio, avatar FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new \Exception("Failed to fetch profile.");
        }
    }
}
