<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\UserModel;
use App\Models\AuthModel;
use App\Utils\Route;
use App\Utils\HttpException;
use App\Middlewares\AuthMiddleware;

class User extends Controller {
  protected object $user;
  protected object $auth;

  public function __construct($param) {
      $this->user = new UserModel();
      $this->auth = new AuthModel();
      parent::__construct($param);
  }

  #[Route("DELETE", "/user/:id", middlewares: [AuthMiddleware::class])]
  public function deleteUser() {
      try {
          $id = intval($this->params['id']);
          if ($id <= 0) {
              throw new HttpException("Invalid user ID.", 400);
          }
          $result = $this->user->delete($id);
          if (!$result) {
              throw new HttpException("Failed to delete user.", 500);
          }
          return ['message' => 'User deleted successfully'];
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("An unexpected error occurred.", 500);
      }
  }

  #[Route("GET", "/users", middlewares: [AuthMiddleware::class])]
  public function getUsers() {
      try {
          $limit = isset($this->params['limit']) ? intval($this->params['limit']) : null;
          return $this->user->getAll($limit);
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("Failed to retrieve users.", 500);
      }
  }

  #[Route("PATCH", "/user/:id", middlewares: [AuthMiddleware::class])]
  public function updateUser() {
      try {
          $id = intval($this->params['id']);
          $data = $this->body;

          if (empty($data)) {
              throw new HttpException("No data provided for the update.", 400);
          }

          $validFields = ['username', 'bio', 'avatar', 'password'];
          $filteredData = array_filter(
              $data,
              fn($key) => in_array($key, $validFields),
              ARRAY_FILTER_USE_KEY
          );

          if (empty($filteredData)) {
              throw new HttpException("No valid fields provided for the update.", 400);
          }

          $result = $this->user->update($filteredData, $id);
          if (!$result) {
              throw new HttpException("Failed to update user.", 500);
          }

          return ['message' => 'User updated successfully'];
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("An unexpected error occurred.", 500);
      }
  }

  #[Route("POST", "/save-drawing", middlewares: [AuthMiddleware::class])]
  public function saveDrawing() {
      try {
          $data = $this->body;
          if (empty($data['id']) || empty($data['draw_svg'])) {
              throw new HttpException("Missing id or draw_svg", 400);
          }
          $this->user->saveDrawing($data['id'], $data['draw_svg']);
          return ['message' => 'Drawing saved successfully'];
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("Failed to save drawing.", 500);
      }
  }

  #[Route("POST", "/save-description", middlewares: [AuthMiddleware::class])]
  public function saveDescription() {
      try {
          $data = $this->body;
          if (empty($data['id']) || empty($data['draw_description'])) {
              throw new HttpException("Missing id or draw_description", 400);
          }
          $this->user->saveDescription($data['id'], $data['draw_description']);
          return ['message' => 'Description saved successfully'];
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("Failed to save description.", 500);
      }
  }

  #[Route("GET", "/user/:id", middlewares: [AuthMiddleware::class])]
  public function getUser() {
      try {
          $id = intval($this->params['id']);
          if ($id <= 0) {
              throw new HttpException("Invalid user ID.", 400);
          }
          return $this->user->getById($id);
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("Failed to retrieve user.", 500);
      }
  }

  #[Route("GET", "/draws/:id", middlewares: [AuthMiddleware::class])]
  public function getDraws() {
      try {
        $id = intval($this->params['id']);
        return $this->user->getDraws($id);
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("Failed to retrieve drawings.", 500);
      }
  }

  #[Route("PATCH", "/user/:id/activated", middlewares: [AuthMiddleware::class])]
  public function updateActive() {
      try {
          $id = intval($this->params['id']);
          $data = $this->body;
          if (!isset($data['activated'])) {
              throw new HttpException("Missing 'activated' field.", 400);
          }
          $result = $this->user->updateActivated($data, $id);
          if (!$result) {
              throw new HttpException("Failed to update activation status.", 500);
          }
          return ['message' => 'User activation updated successfully'];
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("An unexpected error occurred.", 500);
      }
  }

  #[Route("GET", "/username", middlewares: [AuthMiddleware::class])]
  public function getUsername() {
      try {
          return $this->user->getUsername();
      } catch (\Exception $e) {
          error_log('Erreur : ' . $e->getMessage());
          throw new HttpException("Failed to retrieve username.", 500);
      }
  }
  
  #[Route("GET", "/superMatch/:id", middlewares: [AuthMiddleware::class])]
  public function getNumberOfSuperMatch() {
      try {
        $id = intval($this->params['id']);
        return $this->user->getNumberOfSuperMatch($id);
        } catch (\Exception $e) {
          throw new HttpException("Failed to retrieve number of supermatch.", 500);
        }
    }

   #[Route("PATCH", "/superMatch/:id", middlewares: [AuthMiddleware::class])]
   public function setSuperMatch() {
      try {
          $id = intval($this->params['id']);
          $data = $this->body;
          return $this->user->setSuperMatch($id, $data);
      } catch (\Exception $e) {
          throw new HttpException("Failed to set number of supermatch.", 500);
        }
    }

}
