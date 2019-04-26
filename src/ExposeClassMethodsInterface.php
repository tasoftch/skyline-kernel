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

namespace Skyline\Kernel;


use ReflectionMethod;

/**
 * Every method of a class implementing this interface and matching the filter option will be registered.
 * The docComment may use @purpose annotation to specify purposes of that method.
 *
 * @package Skyline\CMS\Service
 */
interface ExposeClassMethodsInterface extends ExposeClassInterface
{
    const FILTER_PUBLIC = ReflectionMethod::IS_PUBLIC;
    const FILTER_PROTECTED = ReflectionMethod::IS_PROTECTED;
    const FILTER_PRIVATE = ReflectionMethod::IS_PRIVATE;
    const FILTER_STATIC = ReflectionMethod::IS_STATIC;
    const FILTER_ABSTRACT = ReflectionMethod::IS_ABSTRACT;
    const FILTER_FINAL = ReflectionMethod::IS_FINAL;
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