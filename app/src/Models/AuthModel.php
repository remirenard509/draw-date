<?php

namespace App\Models;

use App\Models\SqlConnect;
use App\Utils\{HttpException, JWT};
use PDO;

class AuthModel extends SqlConnect {
    private string $userTable = "users";
    private string $adminTable = "admin";
    private int $tokenValidity = 3600 * 24 * 30; // 30 jours en secondes
    private string $passwordSalt;

    public function __construct() {
        parent::__construct();
        $this->passwordSalt = getenv('PASSWORD_SALT') ?: '';
    }

    public function register(array $data): array {
        try {
            $query = "SELECT email FROM {$this->userTable} WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['email' => $data['email']]);

            if ($stmt->rowCount() > 0) {
                throw new HttpException("User already exists!", 400);
            }

            $saltedPassword = $data['password'] . $this->passwordSalt;
            $hashedPassword = password_hash($saltedPassword, PASSWORD_BCRYPT);

            $insert = "INSERT INTO {$this->userTable} 
                        (email, password, username, gender, search_gender, dob, bio) 
                        VALUES (:email, :password, :username, :gender, :search_gender, :dob, :bio)";

            $stmtInsert = $this->db->prepare($insert);
            $stmtInsert->execute([
                'email' => $data['email'],
                'password' => $hashedPassword,
                'username' => $data['username'],
                'gender' => $data['gender'],
                'search_gender' => $data['search_gender'],
                'dob' => $data['dob'],
                'bio' => $data['bio']
            ]);

            $userId = $this->db->lastInsertId();
            $token = $this->generateJWT((int)$userId);

            return ['token' => $token];
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new HttpException("Registration failed.", 500);
        }
    }

    public function login(string $email, string $password): array {
        try {
            // Admin login
            $adminQuery = "SELECT * FROM {$this->adminTable} WHERE email = :email";
            $stmtAdmin = $this->db->prepare($adminQuery);
            $stmtAdmin->execute(['email' => $email]);
            $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

            if ($admin && $this->verifyPassword($password, $admin['password'])) {
                $token = $this->generateJWT((int)$admin['id']);
                return ['token' => $token, 'id' => $admin['id'], 'admin' => true];
            }

            // User login
            $userQuery = "SELECT * FROM {$this->userTable} WHERE email = :email";
            $stmtUser = $this->db->prepare($userQuery);
            $stmtUser->execute(['email' => $email]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user && $this->verifyPassword($password, $user['password'])) {
                $token = $this->generateJWT((int)$user['id']);
                return ['token' => $token, 'id' => $user['id'], 'admin' => false];
            }

            throw new HttpException("Invalid credentials.", 401);
        } catch (\PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage());
            throw new HttpException("Login failed.", 500);
        }
    }

    private function verifyPassword(string $inputPassword, string $hashedPassword): bool {
        $saltedPassword = $inputPassword . $this->passwordSalt;
        return password_verify($saltedPassword, $hashedPassword);
    }

    private function generateJWT(int $userId): string {
        $payload = [
            'id' => $userId,
            'exp' => time() + $this->tokenValidity
        ];

        return JWT::generate($payload);
    }
}
