<?php

namespace Pageon\Stitcher\Response;

use GuzzleHttp\Psr7\Response;

class JsonResponse extends Response
{
    public static function success(array $body = [], array $headers = []) : Response {
        return new self(200, $headers, json_encode($body));
    }

    public static function created() : Response {
        return new self(201);
    }

    public static function accepted() : Response {
        return new self(202);
    }

    public static function notFound() : Response {
        return new self(404);
    }
}
