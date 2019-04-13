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