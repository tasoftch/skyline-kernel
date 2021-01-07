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


use Skyline\Kernel\Exception\SkylineKernelDetailedException;
use Throwable;

class LogErrorHandlerService extends AbstractErrorHandlerService
{
    const EXCEPTION_ERROR_LEVEL = 10;

    private $logFile;

    public function handleError(string $message, int $code, $file, $line, $ctx): bool
    {
        if(file_exists($this->logFile))
            $json = json_decode( file_get_contents($this->logFile), true );
        else
            $json = [];

        $json['E'] = [
            'level' => self::detectErrorLevel($code),
            'code' => $code,
            'message' => $message,
            'file' => SkyDisplayPath($file),
            'line' => $line
        ];

        file_put_contents($this->logFile, json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        return false;
    }

    public function handleException(Throwable $throwable): bool
    {
        if(file_exists($this->logFile))
            $json = json_decode( file_get_contents($this->logFile), true );
        else
            $json = [];

        $error = [
            'level' => self::EXCEPTION_ERROR_LEVEL,
            'code' => $throwable->getCode(),
            'message' => $throwable->getMessage(),
            'file' => SkyDisplayPath($throwable->getFile()),
            'line' => $throwable->getLine()
        ];
        if($throwable instanceof SkylineKernelDetailedException)
            $error["information"] = $throwable->getDetails();

        $json['E'] = $error;

        file_put_contents($this->logFile, json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        return false;
    }

    public function __construct(string $logFile, bool $logEnv = false)
    {
        if(is_file($logFile)) {
            $this->logFile = $logFile;
        } elseif(is_dir($logFile)) {
            $this->logFile = "$logFile/" . date("Y-m-d_G.i.s_") . uniqid() . ".log.php";
        } else {
			error_log(sprintf("ErrorLogger: File or Directory %s does not exist", SkyDisplayPath($logFile)), E_USER_WARNING);
			$this->logFile = "/dev/null";
		}


        if($this->logFile) {
            if(file_exists($this->logFile))
                $json = json_decode( file_get_contents($this->logFile), true );
            else
                $json = [];

            $json['_'] = $logEnv ? $_SERVER : '<no-env>';

            file_put_contents($this->logFile, json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        }
    }
}