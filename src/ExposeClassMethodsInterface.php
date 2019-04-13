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

namespace Skyline\Kernel;


/**
 * Every method of a class implementing this interface and matching the filter option will be registered.
 * The docComment may use @purpose annotation to specify purposes of that method.
 *
 * @package Skyline\CMS\Service
 */
interface ExposeClassMethodsInterface extends ExposeClassInterface
{
    const FILTER_PUBLIC = \ReflectionMethod::IS_PUBLIC;
    const FILTER_PROTECTED = \ReflectionMethod::IS_PROTECTED;
    const FILTER_PRIVATE = \ReflectionMethod::IS_PRIVATE;
    const FILTER_STATIC = \ReflectionMethod::IS_STATIC;
    const FILTER_ABSTRACT = \ReflectionMethod::IS_ABSTRACT;
    const FILTER_FINAL = \ReflectionMethod::IS_FINAL;
    const FILTER_OBJECTIVE = 2048;

    const FILTER_EXCLUDE_MAGIC = 4096;
    const FILTER_PURPOSED_ONLY = 8192;

    const FILTER_PUBLIC_STATIC = self::FILTER_STATIC | self::FILTER_PUBLIC;
    const FILTER_PUBLIC_OBJECTIVE = self::FILTER_PUBLIC | self::FILTER_OBJECTIVE;

    const FILTER_PUBLIC_STATIC_PURPOSED = self::FILTER_STATIC | self::FILTER_PUBLIC | self::FILTER_PURPOSED_ONLY;
    const FILTER_PUBLIC_OBJECTIVE_PURPOSED = self::FILTER_PUBLIC | self::FILTER_OBJECTIVE | self::FILTER_PURPOSED_ONLY;

    /**
     * Returns the methods that should be exposed
     * @return int
     * @see ExposeClassMethodsInterface::FILTER_*
     */
    public static function getMethodFilterOptions(): int;
}