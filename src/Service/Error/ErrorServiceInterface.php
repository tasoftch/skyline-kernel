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

namespace Skyline\Kernel\Service\Error;

/**
 * Interface ErrorServiceInterface is able to handle errors that occured during your application launch
 *
 * @package TASoft\Kernel\Service\Error
 */
interface ErrorServiceInterface
{
    /**
     * Called if an error has occured that should be reported.
     * If this method returns true, Skyline CMS assumes that this error was handled and will continue (except for fatal errors).
     *
     * @param string $message
     * @param int $code
     * @param string $file
     * @param int $line
     * @param $ctx
     * @return bool
     */
    public function handleError(string $message, int $code, $file, $line, $ctx): bool;

    /**
     * Called if an uncaught exception was thrown.
     * If this method returns true, Skyline CMS assumes the exception was handled.
     *
     * @param \Throwable $throwable
     * @return bool
     */
    public function handleException(\Throwable $throwable): bool;
}