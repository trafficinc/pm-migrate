<?php

namespace App\Config;

class Config {

    public $config = [];

    public function __construct() {
        $file = '.config';
        $path = dirname(dirname(__DIR__));
        $filePath = rtrim($path, '/') . '/' . $file;
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Environment file .config not found. Create file with your environment settings at " . $filePath);
        }

        // Read file and get all lines
        $fc = file_get_contents($filePath);
        $lines = explode(PHP_EOL, $fc);

        foreach ($lines as $line) {
            // Only use non-empty lines that look like setters
            if (!empty($line) && strpos($line, '=') !== false) {
                // Strip quotes because putenv can't handle them
                $line = trim(str_replace(array('\'', '"'), '', $line));

                //putenv($line);

                // Set config array
                list($key, $val) = explode('=', $line);
                $this->config[$key] = $val;
            }
        }
    }

    public function getConfig() {
        return $this->config;
    }

}