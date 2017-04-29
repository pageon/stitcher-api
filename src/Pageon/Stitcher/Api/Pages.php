<?php

namespace Pageon\Stitcher\Api;

use GuzzleHttp\Psr7\Response;
use Pageon\Stitcher\Exception\ValidationException;
use Pageon\Stitcher\Response\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Pages extends RestController
{
    /**
     * @var string
     */
    private $srcDir;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * Pages constructor.
     *
     * @param string $srcDir
     */
    public function __construct(string $srcDir) {
        $this->srcDir = $srcDir;
        $this->fs = new Filesystem();
    }

    /**
     * @param null|string $id
     *
     * @return Response
     */
    public function get(string $id = null) : Response {
        $site = $this->getSite();

        if ($id) {
            $page = $this->getPage($id, $site);

            return $page ? JsonResponse::success($page) : JsonResponse::notFound();
        }

        return JsonResponse::success($site);
    }

    /**
     * @return Response
     *
     * @throws ValidationException
     */
    public function post() : Response {
        $data = json_decode($this->request->getBody(), true);

        if (!isset($data['id']) || !isset($data['template'])) {
            throw ValidationException::required(['id', 'template']);
        }

        $saveFile = $this->getSaveFile();
        $fileContents = Yaml::parse($saveFile->getContents());
        $fileContents[$data['id']] = $data;

        $this->fs->dumpFile($saveFile->getPathname(), Yaml::dump($fileContents));

        return JsonResponse::created();
    }

    /**
     * @param string $id
     *
     * @return Response
     */
    public function patch(string $id) : Response {
        $page = $this->getPage($id);

        if (!$page) {
            return JsonResponse::notFound();
        }

        $saveFile = $this->getSaveFile($id);
        $fileContents = Yaml::parse($saveFile->getContents());
        $data = json_decode($this->request->getBody(), true);

        $fileContents[$id] = array_merge($page, $data);

        $this->fs->dumpFile($saveFile->getPathname(), Yaml::dump($fileContents));

        return JsonResponse::success();
    }

    /**
     * @param string $id
     *
     * @return Response
     */
    public function delete(string $id) : Response {
        $page = $this->getPage($id);

        if (!$page) {
            return JsonResponse::notFound();
        }

        $saveFile = $this->getSaveFile($id);
        $fileContents = Yaml::parse($saveFile->getContents());
        unset($fileContents[$id]);

        $this->fs->dumpFile($saveFile->getPathname(), Yaml::dump($fileContents));

        return JsonResponse::accepted();
    }

    /**
     * @return array
     */
    private function getSite() : array {
        /** @var SplFileInfo[] $siteFiles */
        $siteFiles = Finder::create()->files()->in("{$this->srcDir}/site")->name('*.yml');
        $site = [];

        foreach ($siteFiles as $siteFile) {
            try {
                $parsedSiteFile = Yaml::parse($siteFile->getContents());

                $site += (array) $parsedSiteFile;
            } catch (ParseException $e) {
                continue;
            }
        }

        return $site;
    }

    /**
     * @param string     $id
     * @param array|null $site
     *
     * @return array|null
     */
    private function getPage(string $id, array $site = null) {
        $site = $site ?? $this->getSite();

        if (isset($site[$id])) {
            $page = [$id => $site[$id]];
        }

        return $page ?? null;
    }

    /**
     * @param string $pageId
     *
     * @return SplFileInfo
     */
    private function getSaveFile(string $pageId = null) : SplFileInfo {
        $siteFiles = Finder::create()->files()->in("{$this->srcDir}/site")->name('*.yml');

        /** @var SplFileInfo $siteFile */
        foreach ($siteFiles as $siteFile) {
            $parsedSiteFile = Yaml::parse($siteFile->getContents());

            if (!$pageId || isset($parsedSiteFile[$pageId])) {
                return $siteFile;
            }
        }

        $iterator = $siteFiles->getIterator();
        $iterator->rewind();

        return $iterator->current();
    }
}
