<?php

namespace App\Config;

final class Router
{
    private static array $routes = array();
    private object $route;

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
        if (!array_key_exists($path.'get', self::$routes))
        {
            self::$routes[$path.'get'] = (object)[
                "name" => null,
                "path" => $path,
                "function" => $function,
                "method" => 'get'
            ];
            return new Router(self::$routes[$path.'get']);
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
        if (!array_key_exists($path.'post', self::$routes))
        {
            self::$routes[$path.'post'] = (object)[
                "name" => null,
                "path" => $path,
                "function" => $function,
                "method" => 'post'
            ];
            return new Router(self::$routes[$path.'post']);
        }
        die('Error: duplicated route');
    }

    /**
     * Init router
     * @return void
     */
    public static function init(): void
    {
        $url = urldecode(parse_url($_SERVER["REQUEST_URI"])['path']); //get url
        $method = $_SERVER["REQUEST_METHOD"]; //get url method

        //foreach routes
        foreach (self::$routes as $key => $route) {
            //build regex of the route
            $name = preg_replace("/{([a-z]+)}/", "(\w+)", $route->path);
            $name = str_replace("/", "\/", $name);
            $name = "/^" . $name . "$/";

            //checks route
            if (preg_match($name, $url, $matches) and strtolower(trim($key)) == strtolower(trim($url.$method))) {
                //checks route method
                if (strtolower($route->method) == strtolower($method)) {

                    //check route function
                    if (is_callable($route->function)) //if route function is callable
                    {
                        //call route function with args
                        echo $function = call_user_func_array($route->function, array(end($matches)));
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

                                //call route function with args
                                call_user_func_array(function () use ($controller, $action) {

                                    //checks if function exists in controller
                                    if (method_exists($controller, $action)) {
                                        $controller::$action();
                                        exit();
                                    }
                                    //if function not found in controller
                                    die("Error: Method <strong>$action</strong> not defined in <strong>$controller</strong>");

                                }, array(end($matches)));
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
        var_dump(self::$routes);
    }

    /**
     * Method available for the Router instance class
     * Router method for set the route name
     *
     * @param string $name
     * @return void
     */
    public function name(string $name): void
    {
        $key = $this->route->path.$this->route->method;

        if(array_key_exists($key, self::$routes))
        {
            self::$routes[$key]->name = $name;
        }
        else
        {
            die('Route not found');
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