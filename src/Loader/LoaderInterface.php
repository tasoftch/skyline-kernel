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

namespace TASoft\Kernel\Loader;


use Symfony\Component\HttpFoundation\Request;
use TASoft\Config\Config;

/**
 * Bootstrap class to initialize the CMS
 * @package TASoft\Kernel\Loader
 */
interface LoaderInterface
{
    /**
     * LoaderInterface constructor.
     * Must be an empty constructor
     */
    public function __construct();

    /**
     * Passes the whole loaded configuration into the loader.
     * If Skyline CMS is used as web application, the request argument holds information about the requested URI.
     *
     * @param Config $configuration
     * @param Request|null $request
     * @return void
     */
    public function bootstrap(Config $configuration, ?Request $request);
}