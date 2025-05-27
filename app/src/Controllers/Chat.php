<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\UserModel;
use App\Models\AuthModel;
use App\Models\ChatModel;
use App\Utils\Route;
use App\Utils\HttpException;
use App\Middlewares\AuthMiddleware;

class Chat extends Controller {

    private UserModel $user;
    private AuthModel $auth;
    private ChatModel $chat;

    public function __construct($param) {
        parent::__construct($param);
        $this->user = new UserModel();
        $this->auth = new AuthModel();
        $this->chat = new ChatModel();
    }

    #[Route("POST", "/chat", middlewares: [AuthMiddleware::class])]
    public function getChat(): array {
        $data = $this->body;
        return $this->chat->getChat($data['sender_id'], $data['receiver_id']);
    }

    #[Route("POST", "/send", middlewares: [AuthMiddleware::class])]
    public function sendMessage(): bool {
        $data = $this->body;
        $this->chat->sendMessage($data['sender_id'], $data['receiver_id'], $data['content']);
        return true;
    }

    #[Route("POST", "/match", middlewares: [AuthMiddleware::class])]
    public function match(): bool {
        $data = $this->body;
        $this->chat->match($data['user1_id'], $data['user2_id']);
        return true;
    }

    #[Route("GET", "/match/:id", middlewares: [AuthMiddleware::class])]
    public function getMatch(): array {
        return $this->chat->getMatch((int) $this->params['id']);
    }

    #[Route("GET", "/messages/latest/:id", middlewares: [AuthMiddleware::class])]
    public function getLatestMessage(): array {
        return $this->chat->getLastestMessage((int) $this->params['id']);
    }

    #[Route("PATCH", "/messages/read/:id", middlewares: [AuthMiddleware::class])]
    public function setMessageAsRead(): bool {
        $data = $this->body;
        return $this->chat->setMessageAsRead((int) $this->params['id'], $data['sender_id']);
    }

    #[Route("GET", "/displayprofil/:id", middlewares: [AuthMiddleware::class])]
    public function fetchprofil(): array {
        return $this->chat->fetchprofil((int) $this->params['id']);
    }
}
