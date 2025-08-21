<?php
// app/core/Router.php
class Router {
    private $routes = [];
    
    public function add($method, $path, $handler) {
        $this->routes[] = [$method, $path, $handler];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if application is not at root
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        foreach ($this->routes as $route) {
            list($method, $path, $handler) = $route;
            
            if ($requestMethod !== $method) continue;
            
            $pattern = '#^' . preg_replace('#\{[^}]+\}#', '([^/]+)', $path) . '$#';
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                
                if (is_callable($handler)) {
                    call_user_func_array($handler, $matches);
                    return;
                }
                
                list($controller, $method) = explode('@', $handler);
                
                // Check if controller file exists
                $controllerFile = "../app/controllers/$controller.php";
                if (!file_exists($controllerFile)) {
                    http_response_code(404);
                    echo "Controller not found: $controller";
                    return;
                }
                
                require_once $controllerFile;
                
                // Check if controller class exists
                if (!class_exists($controller)) {
                    http_response_code(404);
                    echo "Controller class not found: $controller";
                    return;
                }
                
                $controllerInstance = new $controller();
                
                // Check if method exists
                if (!method_exists($controllerInstance, $method)) {
                    http_response_code(404);
                    echo "Method not found: $method";
                    return;
                }
                
                call_user_func_array([$controllerInstance, $method], $matches);
                return;
            }
        }
        
        http_response_code(404);
        $this->view('errors/404');
    }
    
    private function view($view) {
        if (file_exists("../app/views/$view.php")) {
            require_once "../app/views/$view.php";
        } else {
            echo "Page not found";
        }
    }
}
