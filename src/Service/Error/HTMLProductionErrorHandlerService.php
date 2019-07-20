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

namespace Skyline\Kernel\Service\Error;


use Exception;
use Skyline\Kernel\Exception\SkylineKernelDetailedException;
use Throwable;

class HTMLProductionErrorHandlerService extends AbstractHTTPErrorHandlerService
{
    public function handleError(string $message, int $code, $file, $line, $ctx): bool
    {
        $level = self::detectErrorLevel($code);
        if($level == static::FATAL_ERROR_LEVEL) {
            $e = new Exception($message, 500);
            $this->handleException($e);
            die();
        }
        // Igonre all other kinds of errors and return true, to stop propagation
        return true;
    }

    public function handleException(Throwable $throwable): bool
    {
        $code = $throwable->getCode();

        $code = min(599, max(400, $code));

        $message = $throwable->getMessage();

        $name = $this->getNameOfCode($code);

        if($throwable instanceof SkylineKernelDetailedException) {
            $name = $message;
            $message = $throwable->getDetails();
        }

        if(!$message)
            $message = $this->getDescriptionOfCode($code);

        http_response_code($code);

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