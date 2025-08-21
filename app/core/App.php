<?php
// app/core/App.php
class App {
    private $router;
    
    public function __construct() {
        session_start();
        $this->loadEnv();
        $this->router = new Router();
    }
    
    private function loadEnv() {
        if (file_exists('../.env')) {
            $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                
                list($name, $value) = explode('=', $line, 2);
                $_ENV[trim($name)] = trim($value);
            }
        }
    }
    
    public function getRouter() {
        return $this->router;
    }
    
    public function run() {
        $this->router->dispatch();
    }
}