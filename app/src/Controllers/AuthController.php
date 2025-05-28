<?php 

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\AuthModel;
use App\Utils\{Route, HttpException};

class AuthController extends Controller {
    protected AuthModel $auth;

    public function __construct(array $params) {
        parent::__construct($params);
        $this->auth = new AuthModel();
    }

    #[Route("POST", "/register")]
    public function register(): array {
        try {
            $data = $this->body;
            if (empty($data['email']) || empty($data['password'])) {
                throw new HttpException("Missing email or password.", 400);
            }
            return $this->auth->register($data);
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), 400);
        }
    }

    #[Route("POST", "/login")]
    public function login(): array {
        try {
            $data = $this->body;
            if (empty($data['email']) || empty($data['password'])) {
                throw new HttpException("Missing email or password.", 400);
            }
            return $this->auth->login($data['email'], $data['password']);
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), 401);
        }
    }
}
