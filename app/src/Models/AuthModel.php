<?php

namespace App\Models;

use App\Models\SqlConnect;
use App\Utils\{HttpException, JWT};
use \PDO;

class AuthModel extends SqlConnect {
    private string $userTable = "users";
    private string $adminTable = "admin";
    private int $tokenValidity = 3600 * 24 * 30; // 30 jours en secondes
    private string $passwordSalt;

    public function __construct() {
        parent::__construct();
        $this->passwordSalt = getenv('PASSWORD_SALT') ?: '';
    }

    public function register(array $data) {
        $query = "SELECT email FROM {$this->userTable} WHERE email = :email";
        $req = $this->db->prepare($query);
        $req->execute(["email" => $data["email"]]);

        if ($req->rowCount() > 0) {
            throw new HttpException("User already exists!", 400);
        }

        // Hash du mot de passe avec un "pepper" si défini
        $saltedPassword = $data["password"] . $this->passwordSalt;
        $hashedPassword = password_hash($saltedPassword, PASSWORD_BCRYPT);

        $query_add = "INSERT INTO {$this->userTable} 
            (email, password, username, gender, search_gender, dob, bio) 
            VALUES (:email, :password, :username, :gender, :search_gender, :dob, :bio)";

        $req2 = $this->db->prepare($query_add);
        $req2->execute([
            "email" => $data["email"],
            "password" => $hashedPassword,
            "username" => $data["username"],
            "gender" => $data["gender"],
            "search_gender" => $data["search_gender"],
            "dob" => $data["dob"],
            "bio" => $data["bio"]
        ]);

        $userId = $this->db->lastInsertId();
        $token = $this->generateJWT($userId);

        return ['token' => $token];
    }

    public function login(string $email, string $password) {
        // Admin login
        $queryAdmin = "SELECT * FROM {$this->adminTable} WHERE email = :email";
        $reqAdmin = $this->db->prepare($queryAdmin);
        $reqAdmin->execute(['email' => $email]);
        $admin = $reqAdmin->fetch(PDO::FETCH_ASSOC);

        if ($admin && $this->verifyPassword($password, $admin['password'])) {
            $token = $this->generateJWT($admin['id']);
            return ['token' => $token, 'id' => $admin['id'], 'admin' => true];
        }

        // User login
        $queryUser = "SELECT * FROM {$this->userTable} WHERE email = :email";
        $reqUser = $this->db->prepare($queryUser);
        $reqUser->execute(['email' => $email]);
        $user = $reqUser->fetch(PDO::FETCH_ASSOC);

        if ($user && $this->verifyPassword($password, $user['password'])) {
            $token = $this->generateJWT($user['id']);
            return ['token' => $token, 'id' => $user['id'], 'admin' => false];
        }

        throw new HttpException("Invalid credentials.", 401);
    }

    private function verifyPassword(string $inputPassword, string $hashedPassword): bool {
        $saltedPassword = $inputPassword . $this->passwordSalt;
        return password_verify($saltedPassword, $hashedPassword);
    }

    private function generateJWT(string $userId): string {
        $payload = [
            'id' => $userId,
            'exp' => time() + $this->tokenValidity
        ];
        return JWT::generate($payload);
    }
}
