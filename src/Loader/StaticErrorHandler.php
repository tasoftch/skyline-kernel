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

namespace Skyline\Kernel\Loader;


use Skyline\Kernel\Service\Error\AbstractErrorHandlerService;
use Skyline\Kernel\Service\Error\ErrorServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use TASoft\Config\Config;
use Skyline\Kernel\FileConfig;
use TASoft\Service\ServiceManager;

class StaticErrorHandler implements LoaderInterface
{
    public static $serviceManager;
    private static $errorControllerServiceName;

    public function __construct()
    {
    }

    public function bootstrap(Config $configuration, ?Request $request)
    {
        set_exception_handler(static::class . "::handleException");
        set_error_handler(static::class . "::handleError");

        register_shutdown_function(static::class . "::shutdown");

        ini_set("display_errors", 0);
        ini_set("log_errors", 0);

        if($configuration[FileConfig::CONFIG_DEBUG] ?? false) {
            error_reporting(E_ALL);
        } else {
            error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
        }

        self::$errorControllerServiceName = $configuration[ FileConfig::SERVICE_ERROR_CONTROLLER ] ?? FileConfig::SERVICE_ERROR_CONTROLLER;
    }

    public static function shutdown() {
        $error = error_get_last();
        if($error)
            static::handleError($error["type"], $error["message"], $error["file"], $error["line"], NULL);
    }

    public static function handleException(\Throwable $exception) {
        $SERVICES = static::$serviceManager ?: ServiceManager::generalServiceManager();
        $ec = $SERVICES->get( self::$errorControllerServiceName );

        if($ec) {
            /** @var ErrorServiceInterface $ec */
            if( $ec->handleException($exception) )
                return;
        }

        static::defaultExceptionOutput($exception);
    }

    protected static function defaultExceptionOutput(\Throwable $exception) {
        echo "<pre><b>Uncaught Exception (", get_class($exception) . ")</b> [{$exception->getCode()}]: {$exception->getMessage()}\n</pre>";
    }


    public static function handleError($code, $msg, $file, $line, $ctx) {
        if(error_reporting() & $code) {
            $SERVICES = static::$serviceManager ?: ServiceManager::generalServiceManager();
            $ec = $SERVICES->get( self::$errorControllerServiceName );

            if($ec) {
                /** @var ErrorServiceInterface $ec */
                if( $ec->handleError($msg, $code, $file, $line, $ctx) )
                    return;
            }

            static::defaultErrorOutput($code, $msg, $file, $line, $ctx);
            if(AbstractErrorHandlerService::detectErrorLevel($code) == AbstractErrorHandlerService::FATAL_ERROR_LEVEL)
                exit(255);
        }
    }

    protected static function defaultErrorOutput($code, $msg, $file, $line, $ctx) {
        echo "<pre><b>Error</b> [$code]: $msg</pre>";
    }
}