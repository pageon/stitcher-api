<?php

namespace Pageon\Stitcher;

use Brendt\Stitcher\Plugin\Plugin;

class ApiPlugin implements Plugin
{
    /**
     * Get the location of your plugin's `config.yml` file.
     *
     * @return null|string
     */
    public function getConfigPath() {
        return __DIR__ . '/../../config.yml';
    }

    /**
     * Get the location of your plugin's `services.yml` file.
     *
     * @return null|string
     */
    public function getServicesPath() {
        return __DIR__ . '/../../services.yml';
    }
}
