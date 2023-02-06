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

/**
 * ErrorHandlngTest.php
 * skyline-kernel
 *
 * Created on 2019-04-13 10:58 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Kernel\Config\MainKernelConfig;
use Skyline\Kernel\Service\Error\AbstractErrorHandlerService;
use Skyline\Kernel\Service\Error\ErrorServiceInterface;
use TASoft\Config\Config;
use Skyline\Kernel\Loader\StaticErrorHandler;
use TASoft\Service\ServiceManager;

class ErrorHandlngTest extends TestCase
{
    /** @var StaticErrorHandler */
    private $loader;

    /** @var MyErrHandler */
    private $ec;

    protected function setUp(): void
    {
        parent::setUp();
        $sm = new ServiceManager();
        $sm->myErrorHandler = function() use (&$ec) {return $ec = new MyErrHandler(); };

        $loader = new StaticErrorHandler();
        $loader::$serviceManager = $sm;
        $this->loader= $loader;
        $this->ec =& $ec;
    }

    protected function unload() {
        restore_error_handler();
        restore_exception_handler();
    }

    public function testErrorHandling() {
        $cfg = new Config([
            MainKernelConfig::SERVICE_ERROR_CONTROLLER => 'myErrorHandler',
        ]);
        $this->loader->bootstrap($cfg);

        trigger_error("HEHE", E_USER_NOTICE);

        $this->assertEquals([
            "HEHE",
            E_USER_NOTICE,
            __FILE__,
            __LINE__ - 6
        ], $this->ec->error);

        trigger_error("HA", E_USER_ERROR);

        $this->assertEquals([
            "HA",
            E_USER_ERROR,
            __FILE__,
            __LINE__ - 6
        ], $this->ec->error);

        $this->ec->returnValue = false;


        $output = $this->getActualOutput();
        $this->assertEquals("", $output);

        $this->unload();
    }
}

class MyErrHandler extends AbstractErrorHandlerService implements ErrorServiceInterface {
    public $returnValue = true;

    public $error;
    public $exception;

    public function handleError(string $message, int $code, $file, $line, $ctx): bool
    {
        $this->error = [$message, $code, $file, $line];
        return $this->returnValue;
    }

    public function handleException(Throwable $throwable): bool
    {
        $this->exception = func_get_args();
        return $this->returnValue;
    }
}
