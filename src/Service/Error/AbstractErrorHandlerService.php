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


use Skyline\Kernel\ExposeClassInterface;
use TASoft\Config\Config;
use TASoft\Service\ConfigurableServiceInterface;

abstract class AbstractErrorHandlerService implements ConfigurableServiceInterface, ErrorServiceInterface, ExposeClassInterface
{
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
     * @return Config|array|\ArrayAccess
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
}