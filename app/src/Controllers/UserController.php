<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\UserModel;
use App\Models\AuthModel;
use App\Utils\Route;
use App\Utils\HttpException;
use App\Middlewares\AuthMiddleware;
use App\Validators\UserValidator;

class UserController extends Controller {
    protected object $user;
    protected object $auth;

    public function __construct($param) {
        $this->user = new UserModel();
        $this->auth = new AuthModel();
        parent::__construct($param);
    }

    protected function respondOrFail(bool $success, string $successMsg, string $failMsg): array {
        if (!$success) {
            throw new HttpException($failMsg, 500);
        }
        return ['message' => $successMsg];
    }

    #[Route("DELETE", "/user/:id", middlewares: [AuthMiddleware::class])]
    public function deleteUser() {
        try {
            $id = UserValidator::validateId($this->params['id']);
            return $this->respondOrFail(
                $this->user->delete($id),
                'User deleted successfully',
                'Failed to delete user'
            );
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("An unexpected error occurred.", 500);
        }
    }

    #[Route("GET", "/users", middlewares: [AuthMiddleware::class])]
    public function getUsers() {
        try {
            return $this->user->getAll();
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("Failed to retrieve users.", 500);
        }
    }

    #[Route("PATCH", "/user/:id", middlewares: [AuthMiddleware::class])]
    public function updateUser() {
        try {
            $id = UserValidator::validateId($this->params['id']);
            $filteredData = UserValidator::validateUpdateFields($this->body, ['username', 'bio', 'avatar', 'password']);
            return $this->respondOrFail(
                $this->user->update($filteredData, $id),
                'User updated successfully',
                'Failed to update user'
            );
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("An unexpected error occurred.", 500);
        }
    }

    #[Route("POST", "/save-drawing", middlewares: [AuthMiddleware::class])]
    public function saveDrawing() {
        try {
            UserValidator::requireFields($this->body, ['id', 'draw_svg']);
            $this->user->saveDrawing($this->body['id'], $this->body['draw_svg']);
            return ['message' => 'Drawing saved successfully'];
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("Failed to save drawing.", 500);
        }
    }

    #[Route("POST", "/save-description", middlewares: [AuthMiddleware::class])]
    public function saveDescription() {
        try {
            UserValidator::requireFields($this->body, ['id', 'draw_description']);
            $this->user->saveDescription($this->body['id'], $this->body['draw_description']);
            return ['message' => 'Description saved successfully'];
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("Failed to save description.", 500);
        }
    }

    #[Route("GET", "/user/:id", middlewares: [AuthMiddleware::class])]
    public function getUser() {
        try {
            $id = UserValidator::validateId($this->params['id']);
            return $this->user->getById($id);
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("Failed to retrieve user.", 500);
        }
    }

    #[Route("GET", "/draws/:id", middlewares: [AuthMiddleware::class])]
    public function getDraws() {
        try {
            $id = UserValidator::validateId($this->params['id']);
            return $this->user->getDraws($id);
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("Failed to retrieve drawings.", 500);
        }
    }

    #[Route("PATCH", "/user/:id/activated", middlewares: [AuthMiddleware::class])]
    public function updateActive() {
        try {
            $this->body = json_decode(file_get_contents("php://input"), true);
            $id = UserValidator::validateId($this->params['id']);
            UserValidator::requireFields($this->body, ['activated']);
            return $this->respondOrFail(
                $this->user->updateActivated($this->body, $id),
                'User activation updated successfully',
                'Failed to update activation status'
            );
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
            $id = UserValidator::validateId($this->params['id']);
            return $this->user->getNumberOfSuperMatch($id);
        } catch (\Exception $e) {
            throw new HttpException("Failed to retrieve number of supermatch.", 500);
        }
    }

    #[Route("PATCH", "/superMatch/:id", middlewares: [AuthMiddleware::class])]
    public function setSuperMatch() {
        try {
            $id = UserValidator::validateId($this->params['id']);
            return $this->user->setSuperMatch($id, $this->body);
        } catch (\Exception $e) {
            throw new HttpException("Failed to set number of supermatch.", 500);
        }
    }

    #[Route("POST", "/email")]
    public function getIdFromEmail() {
        return $this->user->getIdFromEmail($this->body);
    }

    #[Route("PATCH", "/userReset/:id")]
    public function resetPassword() {
        try {
            $id = UserValidator::validateId($this->params['id']);
            $filteredData = UserValidator::validateUpdateFields($this->body, ['username', 'bio', 'avatar', 'password']);
            return $this->respondOrFail(
                $this->user->update($filteredData, $id),
                'User updated successfully',
                'Failed to update user'
            );
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("An unexpected error occurred.", 500);
        }
    }
    #[Route("POST", "/save-location/:id")]
    public function savaLocation() {
        try {
            $id = UserValidator::validateId($this->params['id']);
            return $this->user->saveLocation($id, $this->body);
        } catch (\Exception $e) {
            error_log('Erreur : ' . $e->getMessage());
            throw new HttpException("An unexpected error occurred.", 500);
        }
    }
}
