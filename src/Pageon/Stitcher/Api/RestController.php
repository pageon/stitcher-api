<?php

namespace Pageon\Stitcher\Api;

use GuzzleHttp\Psr7\Request;

abstract class RestController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request) {
        $this->request = $request;
    }
}
