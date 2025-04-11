<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\UserModel;
use App\Utils\Route;
use App\Utils\HttpException;
use App\Middlewares\AuthMiddleware;
use App\Utils\JWT;

class User extends Controller {
  protected object $user;

  public function __construct($param) {
    $this->user = new UserModel();

    parent::__construct($param);
  }

  #[Route("DELETE", "/user/:id", middlewares: [AuthMiddleware::class])]
  public function deleteUser() {
    return $this->user->delete(intval($this->params['id']));
  }

  #[Route("GET", "/users", middlewares: [AuthMiddleware::class])]
  public function getUsers() {
      $limit = isset($this->params['limit']) ? intval($this->params['limit']) : null;
      return $this->user->getAll($limit);
  }

  #[Route("PATCH", "/user/:id", middlewares: [AuthMiddleware::class])]
  public function updateUser() {
    try {
      $id = intval($this->params['id']);
      $data = $this->body;

      # Vérifier si des données sont fournies
      if (empty($data)) {
        throw new HttpException("No data provided for the update.", 400);
      }

      # Filtrer les champs valides
      $validFields = ['username', 'bio', 'avatar', 'password'];
      $filteredData = array_filter(
        $data,
        fn($key) => in_array($key, $validFields),
        ARRAY_FILTER_USE_KEY
      );

      # Vérifier si des champs valides sont présents
      if (empty($filteredData)) {
        throw new HttpException("No valid fields provided for the update.", 400);
      }

      # Appeler la méthode de mise à jour dans le modèle
      $result = $this->user->update($filteredData, $id);

      if (!$result) {
        throw new HttpException("Failed to update user.", 500);
      }

      return ['message' => 'User updated successfully'];
    } catch (HttpException $e) {
      throw $e;
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
        error_log('Erreur : ' . $e->getMessage()); // Log de l'erreur
          throw new HttpException($e->getMessage(), 400);
      }
  }

  #[Route("POST", "/save-description", middlewares: [AuthMiddleware::class])]
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
  #[Route("GET", "/user/:id", middlewares: [AuthMiddleware::class])]
  public function getUser() {
      try {
        $id = intval($this->params['id']);
         if (!$this->validateAccess($id)) {
            throw new HttpException("Invalid ID format", 400);
          }
          return $this->user->getById($id);
        } catch (HttpException $e) {
          throw $e;
        }
 } 

  #[Route("GET", "/draws", middlewares: [AuthMiddleware::class])]
  public function getDraws() {
      try {
          return $this->user->getDraws();
      } catch (HttpException $e) {
          throw $e;
      }
  }

  private function validateAccess(int $id) {
    try {
    $id = intval($this->params['id']);
    $token = $this->getHeader();
    $jwt = $token['Authorization'];
    $jwt = str_replace('Bearer ', '', $jwt);
    if(JWT::isExpired($jwt)) {
      throw new HttpException("Token expired", 401);
    }
    if (JWT::verify($jwt) === false) {
      throw new HttpException("Invalid token", 401);
    }
    $payLoad = JWT::getPayLoad($jwt);    
    $idFromToken = $payLoad['id'];
    if ($id === intval($idFromToken)) {
      return true;
    }
    return false;
    } catch (HttpException $e) {
      throw $e;
    } catch (\Exception $e) {
      error_log('Erreur : ' . $e->getMessage());
      throw new HttpException("An unexpected error occurred.", 500);
    }
  }
  
}
