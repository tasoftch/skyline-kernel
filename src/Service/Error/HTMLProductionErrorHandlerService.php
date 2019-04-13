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


use Skyline\Kernel\Exception\SkylineKernelDetailedException;

class HTMLProductionErrorHandlerService extends AbstractHTTPErrorHandlerService
{
    public function handleError(string $message, int $code, $file, $line, $ctx): bool
    {
        $level = self::detectErrorLevel($code);
        if($level == static::FATAL_ERROR_LEVEL) {
            $e = new \Exception($message, 500);
            $this->handleException($e);
            die();
        }
        // Igonre all other kinds of errors and return true, to stop propagation
        return true;
    }

    public function handleException(\Throwable $throwable): bool
    {
        $code = $throwable->getCode();
        $message = $throwable->getMessage();

        $name = $this->getNameOfCode($code);

        if($throwable instanceof SkylineKernelDetailedException) {
            $name = $message;
            $message = $throwable->getDetails();
        }

        if(!$message)
            $message = $this->getDescriptionOfCode($code);


        echo '<html style="height:100%" lang="en">
<head><title> '.$code.' '.$name.'
</title></head>
<body style="color: #444; margin:0;font: normal 1em Arial, Helvetica, sans-serif; height:100%; background-color: #fff;">
<div style="height:auto; min-height:100%; ">     <div style="text-align: center; width:24em; margin-left: -12em; position:absolute; top: 30%; left:50%;">
        <h1 style="margin:0; font-size:10em; line-height:1em; font-weight:bold;">'.$code.'</h1>
<h2 style="margin-top:20px;font-size: 30px;">'.$name.'
</h2>
<p>'.$message.'</p>
</div></div></body></html>';
        return true;
    }
}