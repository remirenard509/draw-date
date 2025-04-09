<?php 

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\AuthModel;
use App\Utils\{Route, HttpException};

class Auth extends Controller {
  protected object $auth;

  public function __construct($params) {
    $this->auth = new AuthModel();
    parent::__construct($params);
  }


  #[Route("POST", "/register")]
  public function register() {
      try {
          $data = $this->body;
          if (empty($data['email']) || empty($data['password'])) {
              throw new HttpException("Missing data", 400);
          }
          $user = $this->auth->register($data);
          return $user;
      } catch (\Exception $e) {
          throw new HttpException($e->getMessage(), 400);
      }
  }

  #[Route("POST", "/login")]
  public function login() {
      try {
          $data = $this->body;
          if (empty($data['email']) || empty($data['password'])) {
              throw new HttpException("Missing email or password.", 400);
          }
          $token = $this->auth->login($data['email'], $data['password']);
          return $token;
      } catch (\Exception $e) {
          throw new HttpException($e->getMessage(), 401);
      }
  }
  
}