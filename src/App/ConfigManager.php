<?php

namespace uarsoftware\dbpatch\App;
use uarsoftware\dbpatch\Util\Util;

class ConfigManager {

    protected $configs = array();

    public function __construct() {
    }

    public function configFullPath($configPath,$basePath) {

        $fullPath = realpath($configPath);
        if ($fullPath !== false) {
            return $fullPath;
        }

        $fullPath = $basePath . DIRECTORY_SEPARATOR . $configPath;
        $fullPath = Util::getAbsolutePath($fullPath);
        $fullPath = realpath($fullPath);

        if ($fullPath === false) {
            throw new \exception("Full path retrieval for the config file failed, config file or path doesn't exist");
        }

        return $fullPath;
    }

    public function findConfigFile($rootPath,$configFileName = 'config.php') {
        $result = Util::recursiveDirectoryFileSearch($rootPath,$configFileName);

        if ($result === false) {
            throw new \exception("Could not find a file at {$rootPath} called {$configFileName}");
        }

        return $result;
    }

    public function getConfig($path) {
        if (!file_exists($path)) {
            throw new \exception("Config file loading failed. Config file does not exist at {$path}");
        }
        $config = require($path);
        return $config;
    }
}
