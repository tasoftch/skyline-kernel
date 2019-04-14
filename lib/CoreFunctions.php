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

use Skyline\Kernel\Config\MainKernelConfig;
use TASoft\Config\Config;
use TASoft\Service\Exception\ServiceException;
use TASoft\Service\ServiceManager;


/**
 * Returns the main configuration
 * The configuration is available after loading the configuration and while bootstrapping Skyline CMS
 *
 * @return null|Config
 */
function SkyMainConfig(): ?Config {
    global $_MAIN_CONFIGURATION;
    return $_MAIN_CONFIGURATION;
}

/**
 * Gets a value from the configuration.
 * This function will return the resolved value or an array with resolved values.
 * Resolved values are:
 *  $value      ( ^\$[a-z_][a-zA-Z0-9_]*$ )     => is resolved to the service instance value
 *  %param%     ( ^%.+%$ )                      => is resolved to the requested parameter of service manager
 *  $(...)      ( \$\(.*?\) )                   => is resolved to a location declared in main configuration under key FileConfig::CONFIG_LOCATIONS
 *
 * @param $key
 * @param mixed|null $default
 * @return array|iterable|mixed
 */
function SkyMainConfigGet($key, $default = NULL) {
    $config = SkyMainConfig();
    $value = $config[$key] ?? $default;
    try {
        $value = ServiceManager::generalServiceManager()->mapValue($value, true);
    } catch (ServiceException $exception) {}

    if(is_string($value) && strpos($value, '$(') !== false) {
        return SkyGetPath($value, false);
    }

    return $value;
}

/**
 * Searches in locations for the required location and concats appendix if found
 *
 * @param $location
 * @param string $appendix
 * @return bool|string
 */
function SkyGetLocation($location, $appendix = '') {
    $p = SkyMainConfig() [ MainKernelConfig::CONFIG_LOCATIONS ][$location] ?? "#";
    if($appendix)
        $p .= "/$appendix";
    return realpath($p);
}

/**
 * Resolves any $(...) location specifier to its absolute path
 *
 * @param $path
 * @return string|bool
 */
function SkyGetPath($path, bool $real = true) {
    while(strpos($path, '$(') !== false) {
        $path = preg_replace_callback("/\\$\(([^\)]+)\)/i", function($ms) {
            global $_MAIN_CONFIGURATION;

            $loc = $_MAIN_CONFIGURATION[ MainKernelConfig::CONFIG_LOCATIONS ][$ms[1]] ?? NULL;
            if($loc) {
                return $loc;
            }
            trigger_error("Location $ms[1] not found", E_USER_WARNING);
            return NULL;
        }, $path);
    }
    return $real ? realpath($path) : $path;
}

/**
 * Use this function to make sure, that you do not show full paths to the client.
 * Under debugging, the full path is returned but in production, this function only shows a relative path
 *
 * @param $path
 * @return string
 */
function SkyDisplayPath($path) {

}