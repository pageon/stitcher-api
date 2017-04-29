<?php

namespace Pageon\Stitcher\Application;

use Brendt\Stitcher\App;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class ApiApplicationTest extends TestCase
{
    /**
     * @var ApiApplication
     */
    private $api;

    public function setUp() {
        $this->api = App::init('./tests/config.yml')::get('app.api');
    }

    public function test_basic_dispatch() {
        $response = $this->api->run(new Request('GET', '/pages'));

        d($response->getBody()->getContents());
    }

    public function test_detail_dispatch() {
        $response = $this->api->run(new Request('GET', '/pages/my-slug/abc'));
        
        d($response->getBody()->getContents());
    }
}
