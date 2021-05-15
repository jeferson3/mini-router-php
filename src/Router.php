<?php

namespace App;

final class Router
{

    /**
     * @var array
     */
    private static array $routes = array();

    /**
     * @var object|array
     */
    private $route;

    public function __construct($route)
    {
        $this->route = $route;
    }

    /**
     * @param string $path
     * @param callable|string $function
     * @return Router
     */
    public static function  get(string $path, $function): Router
    {
        $key = md5($path.'get');

        if (!array_key_exists($key, self::$routes))
        {
            self::$routes[$key] = (object)[
                "name" => null,
                "path" => $path,
                "function" => $function,
                "method" => 'get'
            ];
            return new Router(self::$routes[$key]);
        }
        die('Error: duplicated route');
    }

    /**
     * @param string $path
     * @param callable|string $function
     * @return Router
     */
    public static function post(string $path, $function): Router
    {
        $key = md5($path.'post');

        if (!array_key_exists($key, self::$routes))
        {
            self::$routes[$key] = (object)[
                "name" => null,
                "path" => $path,
                "function" => $function,
                "method" => 'post'
            ];
            return new Router(self::$routes[$key]);
        }
        die('Error: duplicated route');
    }

    /**
     * @param string $path
     * @param callable|string $function
     * @return Router
     */
    public static function put(string $path, $function): Router
    {
        $key = md5($path.'put');

        if (!array_key_exists($key, self::$routes))
        {
            self::$routes[$key] = (object)[
                "name" => null,
                "path" => $path,
                "function" => $function,
                "method" => 'put'
            ];
            return new Router(self::$routes[$key]);
        }
        die('Error: duplicated route');
    }

    /**
     * @param string $path
     * @param callable|string $function
     * @return Router
     */
    public static function delete(string $path, $function): Router
    {
        $key = md5($path.'delete');

        if (!array_key_exists($key, self::$routes))
        {
            self::$routes[$key] = (object)[
                "name" => null,
                "path" => $path,
                "function" => $function,
                "method" => 'delete'
            ];
            return new Router(self::$routes[$key]);
        }
        die('Error: duplicated route');
    }

    /**
     * @param string $path
     * @param string $controller
     * @return Router
     */
    public static function resource(string $path, string $controller): Router
    {

        if (preg_match("/^([a-zA-Z]+)$/", $controller))
        {
            if (!preg_match("/^\/\w+$/",$path))
            {
                $path = "/".$path;
            }
            $listRoutes = [
                self::get($path, $controller.'@index'),
                self::get($path.'/create', $controller.'@create'),
                self::post($path.'/store', $controller.'@store'),
                self::get($path.'/{id}/show', $controller.'@show'),
                self::get($path.'/{id}/edit', $controller.'@edit'),
                self::put($path.'/{id}/update', $controller.'@update'),
                self::delete($path.'/{id}/delete', $controller.'@delete')
            ];
            return new Router($listRoutes);
        }
        die("Error: Incorrect controller format: $controller");
    }

    /**
     * Init router
     * @return void
     */
    public static function init(): void
    {
        $url = urldecode(parse_url($_SERVER["REQUEST_URI"])['path']); //get url

        //get url method
        $method = (isset($_POST['_method']) and !empty($_POST['_method'])) ? strtolower(trim($_POST['_method'])) : strtolower($_SERVER["REQUEST_METHOD"]);

        //foreach routes
        foreach (self::$routes as $key => $route) {
            //build regex of the route
            $name = preg_replace("/{([a-z]+)}/", "([^/|^<|^>|^ ]+)", $route->path);
            $name = str_replace("/", "\/", $name);
            $name = "/^" . $name . "$/";

            //checks route
            if (preg_match($name, $url, $matches) and $key == md5($route->path.$method)) {
                //checks route method
                if (strtolower($route->method) == strtolower($method)) {

                    //check route function
                    if (is_callable($route->function)) //if route function is callable
                    {
                        unset($matches[0]);//remove route to be just the parameters

                        //call route function with args
                        call_user_func_array($route->function, $matches);
                        exit();
                    }
                    //if route function not callable
                    else {
                        //checks controller string format
                        if (preg_match("/([a-zA-Z]+)@([a-zA-Z]+)/", $route->function)) {
                            $aux = explode("@", $route->function);
                            $controller = $aux[0]; //controller
                            $action = $aux[1]; //method of controller

                            //directory of controller file
                            $dir = $_SERVER["DOCUMENT_ROOT"] . "/src/Controllers/" . $controller;

                            $controller = "\\App\\Controllers\\" . $controller; //namespace of controller

                            //checks if file exists
                            if (file_exists($dir . ".php")) {

                                //checks if function exists in controller
                                if (method_exists($controller, $action)) {

                                    unset($matches[0]); //remove route to be just the parameters

                                    //call route function with args
                                    call_user_func_array([$controller, $action], $matches);
                                    exit();
                                }
                                //if function not found in controller
                                die("Error: Method <strong>$action</strong> not defined in <strong>$controller</strong>");

                            }
                            //if file doesn't exists
                            else {
                                die("Error: Controller not found <strong>$controller</strong>");

                            }
                        }
                        //if controller format string don't match
                        else {
                            die("Error: Incorrect controller format: <strong>" . $route->function . "</strong>");
                        }
                    }

                }
                //if method not equal's
                else {
                    //returns error method not allowed
                    http_response_code(405);
                    exit();
                }
            }
        }
        //if route not found, returns pageNotFound() function
        self::pageNotFound();
    }

    /**
     * Return response code 404;
     */
    public static function pageNotFound(): void
    {
        //returns error of not found
        http_response_code(404);
    }

    /**
     * Return list of routes
     */
    public static function getRoutes(): void
    {
        foreach (self::$routes as $route)
        {
            print_r($route);
            echo "<br>";
        }
    }

    /**
     * Method available for the Router instance class -
     * Router method for set the route name
     *
     * @param string $name
     * @return void
     */
    public function name(string $name): void
    {
        if (!is_array($this->route))
        {
            $key = md5($this->route->path.$this->route->method);

            if(array_key_exists($key, self::$routes))
            {
                self::$routes[$key]->name = $name;
            }
            else
            {
                die('Route not found');
            }
        }
        else{

            foreach ($this->route as $router)
            {
                $method = explode("@",$router->route->function)[1];
                $router->name($name.".".$method);
            }

        }
    }

    /**
     * function to get url of the route using route name
     *
     * @param string $name
     * @param string|int $arg
     * @return string
     */
    public static function route(string $name, $arg=null): string
    {
        if (!empty(self::$routes))
        {
            foreach (self::$routes as $route)
            {
                if ($name == $route->name)
                {
                    if (preg_match("/{([a-z]+)}/", $route->path, $matches))
                    {
                        $param = end($matches);

                        if (is_null($arg))
                        {
                            die("The parameter <strong>$param</strong> is required in route <strong>$route->name</strong>");
                        }
                        return $aux = preg_replace("/{([a-z]+)}/", $arg, $route->path);
                    }
                    else
                    {
                        return $route->path;
                    }
                }
            }
        }
        die("Error: undefined route <strong>$name</strong>");
    }
}
