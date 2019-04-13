<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Skyline\Kernel;


abstract class FileConfig
{
    // GLOBAL CONFIG KEYS
    /** @var string bool to signalize debug mode */
    const CONFIG_DEBUG = 'Debug';
    /** @var string bool to signalize test mode (is in production, but in test mode) */
    const CONFIG_TEST = 'Test';
    const CONFIG_LOADERS = 'loaders';
    const CONFIG_SERVICES = 'services';
    const CONFIG_PHP_SESSION = 'PHP_SESSION';
    const CONFIG_VERSION = 'version';

    // Predefined Services
    const SERVICE_ERROR_CONTROLLER = 'errorController';


    public static function loadVersion($composerFileName) {
        $cmp = file_get_contents($composerFileName);
        $json = json_decode($cmp, true);
        $version = $json["version"] ?? false;
        if($version === false)
            trigger_error("No version entry found in composer file $composerFileName", E_USER_WARNING);
        return $version;
    }

    public static function getSkylineVersion(): string {
        return static::loadVersion(__DIR__ . "/../../composer.json");
    }

    public static function getPackageVersion($package = NULL) {
        if(!$package) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $trace = array_shift($trace);

            if($file = $trace["file"] ?? false) {
                while(strlen($file = dirname($file)) > 1) {
                    if(file_exists("$file/composer.json")) {
                        return static::loadVersion("$file/composer.json");
                    }
                }
            }
        } elseif(is_dir($vendorDir = getcwd() . "/vendor/$package")) {
            if(file_exists("$vendorDir/composer.json")) {
                return static::loadVersion("$vendorDir/composer.json");
            }
        }
        trigger_error("No package found for $package", E_USER_WARNING);
        return false;
    }
}