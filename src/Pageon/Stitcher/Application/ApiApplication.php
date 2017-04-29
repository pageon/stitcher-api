<?php

namespace Pageon\Stitcher\Application;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pageon\Stitcher\Api\RestController;

class ApiApplication {

    public function __construct(RouteCollector $routeCollector) {
        $this->routeCollector = $routeCollector;
        // TODO: via services
        $this->dispatcher = new GroupCountBased($routeCollector->getData());
    }

    public function run(Request $request = null) : Response {
        if (!$request) {
            $request = new Request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
        }
        
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        $response = null;

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response = new Response(404);

                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];

                $response = new Response(405);

                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                if ($handler instanceof RestController) {
                    $handler->setRequest($request);
                }

                /** @var Response $response */
                $response = call_user_func_array($handler, $vars);

                break;
        }

        return $response;
    }

}
