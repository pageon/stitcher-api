<?php

namespace Pageon\Stitcher\Api;

use Brendt\Stitcher\App;
use GuzzleHttp\Psr7\Request;
use Pageon\Test\ApiTest;
use Symfony\Component\Yaml\Yaml;

class PagesTest extends ApiTest
{
    /**
     * @var Pages
     */
    private $pages;

    protected function setUp() {
        parent::setUp();

        $this->pages = App::get('api.pages');
    }

    public function test_pages_overview() {
        $response = $this->pages->get();

        $pages = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('/', $pages);
        $this->assertArrayHasKey('/churches', $pages);
        $this->assertArrayHasKey('/churches/{id}', $pages);
    }

    public function test_pages_detail() {
        $response = $this->pages->get('/churches/{id}');

        $pages = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('/churches/{id}', $pages);
        $this->assertArrayNotHasKey('/', $pages);
        $this->assertArrayNotHasKey('/churches', $pages);
    }

    public function test_pages_detail_404() {
        $response = $this->pages->get('/not_found');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_post() {
        $this->pages->setRequest(new Request('POST', '/pages', [], json_encode([
            'id'       => '/pages/test',
            'template' => 'index',
        ])));

        $response = $this->pages->post();
        $site = Yaml::parse(@file_get_contents(__DIR__ . '/../../../src/site/site.yml'));

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('/pages/test', $site);
    }

    /**
     * @expectedException \Pageon\Stitcher\Exception\ValidationException
     */
    public function test_post_validation_exception_no_id() {
        $this->pages->setRequest(new Request('POST', '/pages', [], json_encode([
            'template' => 'index',
        ])));

        $this->pages->post();
    }

    /**
     * @expectedException \Pageon\Stitcher\Exception\ValidationException
     */
    public function test_post_validation_exception_no_template() {
        $this->pages->setRequest(new Request('POST', '/pages', [], json_encode([
            'id' => '/pages/test',
        ])));

        $this->pages->post();
    }

    public function test_patch() {
        $this->pages->setRequest(new Request('PATCH', '/pages/churches/{id}', [], json_encode([
            'template' => 'test-template',
            'variables' => [
                'my-test' => 'TEST'
            ]
        ])));

        $response = $this->pages->patch('/churches/{id}');
        $site = Yaml::parse(@file_get_contents(__DIR__ . '/../../../src/site/site.yml'));
        $page = $site['/churches/{id}'];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('/', $site);
        $this->assertTrue(isset($page['variables']['my-test']));
        $this->assertEquals('test-template', $page['template']);
    }

    public function test_patch_not_found() {
        $response = $this->pages->patch('/not-found');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_delete() {
        $response = $this->pages->delete('/churches/{id}');
        $site = Yaml::parse(@file_get_contents(__DIR__ . '/../../../src/site/site.yml'));

        $this->assertEquals(202, $response->getStatusCode());
        $this->assertArrayNotHasKey('/churches/{id}', $site);
    }

    public function test_delete_not_found() {
        $response = $this->pages->delete('/not-found');

        $this->assertEquals(404, $response->getStatusCode());
    }
}
