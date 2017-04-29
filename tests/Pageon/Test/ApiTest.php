<?php

namespace Pageon\Test;

use Brendt\Stitcher\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ApiTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct() {
        parent::__construct();

        $this->fs = new Filesystem();
    }

    protected function setUp() {
        $this->copyFolder(__DIR__ . '/../../_src', __DIR__ . '/../../src');

        App::init('./tests/config.yml');
    }

    protected function tearDown() {
        if ($this->fs->exists('./tests/src')) {
            $this->fs->remove('./tests/src');
        }
    }

    private function copyFolder($src, $dst) {
        $finder = new Finder();
        /** @var SplFileInfo[] $srcFiles */
        $srcFiles = $finder->files()->in($src)->ignoreDotFiles(false);

        if (!$this->fs->exists($dst)) {
            $this->fs->mkdir($dst);
        }

        foreach ($srcFiles as $srcFile) {
            if (!$this->fs->exists("{$dst}/{$srcFile->getRelativePath()}")) {
                $this->fs->mkdir("{$dst}/{$srcFile->getRelativePath()}");
            }

            $path = $srcFile->getRelativePathname();
            if (!$this->fs->exists("{$dst}/{$path}")) {
                $this->fs->touch("{$dst}/{$path}");
            }

            $this->fs->dumpFile("{$dst}/{$path}", $srcFile->getContents());
        }
    }
}
