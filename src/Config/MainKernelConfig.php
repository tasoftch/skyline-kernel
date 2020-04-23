<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Skyline\Kernel\Config;


abstract class MainKernelConfig
{
    // GLOBAL CONFIG KEYS

    const CONFIG_LOADERS = 'loaders';
    const CONFIG_SERVICES = 'services';
    const CONFIG_LOCATIONS = 'locations';

    const CONFIG_VERSION = 'version';

    // Predefined Services
    const SERVICE_ERROR_CONTROLLER = 'errorController';
    const SERVICE_DEPENDENCY_MANAGER = 'dependencyManager';
    const SERVICE_EVENT_MANAGER = 'eventManager';

    /**
     * Loads the version from a composer.json file
     *
     * @param $composerFileName
     * @return bool
     */
    public static function loadVersion($composerFileName) {
        $cmp = file_get_contents($composerFileName);
        $json = json_decode($cmp, true);
        $version = $json["version"] ?? false;
        if($version === false)
            trigger_error("No version entry found in composer file $composerFileName", E_USER_WARNING);
        return $version;
    }

    /**
     * Return this Skyline CMS version
     * @return string
     */
    public static function getSkylineVersion(): string {
        return '0.8.1';
    }

    /**
     * Tries to obtain the version of a composer package
     *
     * @param null $package
     * @return bool
     */
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