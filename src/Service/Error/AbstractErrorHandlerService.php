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


use ArrayAccess;
use Skyline\Kernel\ExposeClassInterface;
use TASoft\Config\Config;
use TASoft\Service\ConfigurableServiceInterface;

abstract class AbstractErrorHandlerService implements ConfigurableServiceInterface, ErrorServiceInterface, ExposeClassInterface
{
    protected static $errorCodes = [
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_PARSE             => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED'
    ];


    const PURPOSE = 'errorHandler';

    const NOTICE_ERROR_LEVEL = 0;
    const DEPRECATED_ERROR_LEVEL = 1;
    const WARNING_ERROR_LEVEL = 2;
    const FATAL_ERROR_LEVEL = 3;

    private $configuration;

    /**
     * @inheritDoc
     */
    public function setConfiguration($configuration)
    {
       $this->configuration = $configuration;
    }

    /**
     * @return Config|array|ArrayAccess
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    public static function getPurposes(): array
    {
        return [
            static::PURPOSE
        ];
    }

    /**
     * Classifies an internal PHP error code into a level like notices, warnings or errors
     *
     * @param int $internErrorCode
     * @return int
     */
    public static function detectErrorLevel(int $internErrorCode): int {
        switch ($internErrorCode) {
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_STRICT: return static::WARNING_ERROR_LEVEL;

            case E_NOTICE:
            case E_USER_NOTICE: return static::NOTICE_ERROR_LEVEL;

            case E_DEPRECATED:
            case E_USER_DEPRECATED: return static::DEPRECATED_ERROR_LEVEL;

            default:
                break;
        }

        return static::FATAL_ERROR_LEVEL;
    }

    /**
     * Tries to resolve a php intern error code into a string representation of the error
     *
     * @param int $internErrorCode
     * @return string
     */
    public static function detectErrorName(int $internErrorCode): string {
        return static::$errorCodes[$internErrorCode] ?? "$internErrorCode";
    }
}