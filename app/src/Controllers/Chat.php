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

  public function __construct($param) {
      $this->user = new UserModel();
      $this->auth = new AuthModel();
      $this->chat = new ChatModel();
      parent::__construct($param);
  }

  #[Route("GET", "/chat/:id", middlewares: [AuthMiddleware::class])]
  public function getChat() {
    $id = intval($this->params['id']);
    return $this->chat->getChatFromId($id);
  }

  #[Route("POST", "/send", middlewares: [AuthMiddleware::class])]
  public function sendMessage() {
    $data = $this->body;
    $senderId = $data['sender_id'];
    $receiverId = $data['receiver_id'];
    $content = $data['content'];
    $this->chat->sendMessage($senderId, $receiverId, $content);
    return true;
  }
}