<?php
// app/controllers/Controller.php
class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    protected function view($view, $data = []) {
        extract($data);
        require_once "../app/views/$view.php";
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
    
    protected function hasRole($role) {
        if (!$this->isAuthenticated()) return false;
        
        $userRole = $_SESSION['user_role'] ?? '';
        return $userRole === $role || $userRole === 'Admin';
    }
    
    protected function requireRole($role) {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            http_response_code(403);
            echo "Access denied. Required role: $role";
            exit;
        }
    }
    
    protected function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    protected function validateCsrf() {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(419);
            echo "CSRF token validation failed";
            exit;
        }
    }
}
