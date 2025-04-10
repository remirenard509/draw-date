<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\UserModel;
use App\Utils\Route;
use App\Utils\HttpException;
use App\Middlewares\AuthMiddleware;

class User extends Controller {
  protected object $user;

  public function __construct($param) {
    $this->user = new UserModel();

    parent::__construct($param);
  }

  #[Route("DELETE", "/user/:id")]
  public function deleteUser() {
    return $this->user->delete(intval($this->params['id']));
  }

  #[Route("GET", "/users", middlewares: [AuthMiddleware::class])]
  public function getUsers() {
      $limit = isset($this->params['limit']) ? intval($this->params['limit']) : null;
      return $this->user->getAll($limit);
  }

  #[Route("PATCH", "/user/:id")]
  public function updateUser() {
    try {
      $id = intval($this->params['id']);
      $data = $this->body;

      # Check if the data is empty
      if (empty($data['username']) ||
          empty($data['bio']) ||
          empty($data['avatar'])) {
        throw new HttpException("Missing parameters for the update.", 400);
      }

      return $this->user->update($data, intval($id));
    } catch (HttpException $e) {
      throw $e;
    }
  }

  #[Route("POST", "/save-drawing")]
  public function saveDrawing() {
      try {
          $data = $this->body;
          if (empty($data['id']) || empty($data['draw_svg'])) {
              throw new HttpException("Missing id or draw_svg", 400);
          }
          $this->user->saveDrawing($data['id'], $data['draw_svg']);
          return ['message' => 'Drawing saved successfully'];
      } catch (\Exception $e) {
        error_log('Erreur : ' . $e->getMessage()); // Log de l'erreur
          throw new HttpException($e->getMessage(), 400);
      }
  }

  #[Route("POST", "/save-description")]
  public function saveDescription() {
      try {
          $data = $this->body;
          if (empty($data['id']) || empty($data['draw_description'])) {
              throw new HttpException("Missing id or description", 400);
          }
          $this->user->saveDescription($data['id'], $data['draw_description']);
          return ['message' => 'Description saved successfully'];
      } catch (\Exception $e) {
        error_log('Erreur : ' . $e->getMessage()); // Log de l'erreur
          throw new HttpException($e->getMessage(), 400);
      }
  }
  #[Route("GET", "/user/:id")]
  public function getUserByID() {
      try {
          $id = intval($this->params['id']);
          return $this->user->get($id);
      } catch (HttpException $e) {
          throw $e;
      }
  }
  #[Route("GET", "/users")]
  public function getIdByEmail() {
      try {
          $email = $this->params['email'];
          return $this->user->getIdByEmail($email);
      } catch (HttpException $e) {
          throw $e;
      }
  }
}
