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

namespace Skyline\Kernel\Service;


use Symfony\Component\HttpFoundation\Request;

final class CORSService
{
	private static $hosts;
	public static $acceptsFromReferer = true;
	/**
	 * Registers a host name for your application.
	 * The Skyline CMS will use this host lists to determine if a request is authorized to obtain data.
	 * For example the Public directory of all modules are only accessible for requests of registered hosts.
	 *
	 * @param string $host
	 * @param string|NULL $acceptsFrom
	 * @param bool $withCredentials
	 * @param string|null $label            A label to get the host in your scripts
	 */
	public static function registerHost(string $host, $acceptsFrom = "", bool $withCredentials = NULL, string $label = NULL) {
		$add = function($acceptsFrom) use ($host, $withCredentials, $label) {
			if($acceptsFrom != "" && $withCredentials === NULL)
				$withCredentials = true;

			self::$hosts[ $host ] [$acceptsFrom] = $withCredentials;
			if($label)
				self::$hosts[ $host ] ["@@"] = $label;
		};

		if(is_array($acceptsFrom)) {
			foreach($acceptsFrom as $value)
				$add($value);
		} else {
			$add($acceptsFrom);
		}
	}

	/**
	 * Returns the host described by a label.
	 * Note that hosts can be labelled in the project.xml file for compilation.
	 * The labels are only available inside your application, to get them on demand.
	 *
	 * @param string $label
	 * @param string $default
	 * @return int|string
	 */
	public static function getHostByLabel(string $label, $default = "") {
		foreach (self::$hosts as $hname => $host) {
			if(isset($host["@@"]) && $host["@@"] == $label)
				return $hname;
		}
		return $default;
	}

	/**
	 * Determines the used scheme of a request
	 *
	 * @param Request $request
	 * @return string
	 */
	public static function getScheme(Request $request) {
		return $request->isSecure() ? 'https' : 'http';
	}

	/**
	 * Returns the host described by a label.
	 * Additionally it will prepend http:// or https:// depending if the request is secure or not.
	 *
	 * @param string $label
	 * @param Request $request
	 * @param string $default
	 * @return string
	 */
	public static function getHostAndSchemeByLabel(string $label, Request $request, $default = NULL) {
		$host = self::getHostByLabel($label, NULL == $default ? $request->getHttpHost():$default);
		if($host) {
			return sprintf("%s://%s", self::getScheme($request), $host);
		}
		return "";
	}

	/**
	 * Decide, if the request's origin is accepted by this application
	 *
	 * @param Request $request
	 * @param $withCredentials
	 * @return string|null
	 */
	public static function getAllowedOriginOf(Request $request, &$withCredentials) {
		self::getHostOfRequest($request, $serverHost);
		$withCredentials = false;

		if(($info = static::$hosts[$serverHost] ?? false) && self::getOriginOfRequest($request, $host, $scheme)) {

			foreach($info as $pattern => $withC) {
				if(fnmatch($pattern, $host)) {
					$withCredentials = $withC;
					return "$scheme://$host";
				}
			}
		}

		return NULL;
	}

	/**
	 * Fetches the origin (or if enabled the referer) header field of a request and creates a valid origin string
	 *
	 * @param Request $request
	 * @param string $host
	 * @param string $scheme
	 * @return string|null
	 */
	public static function getOriginOfRequest(Request $request, string &$host = NULL, string &$scheme = NULL): ?string {
		// According to CORS protocol, a cross origin request must declare its origin
		$origin = $request->headers->get("ORIGIN");
		if(!$origin && self::$acceptsFromReferer) {
			// Skyline also accepts from referer
			$origin = $request->headers->get("REFERER");
		}

		if(!$origin)
			return NULL;

		$origin = parse_url($origin);

		$host = $origin["host"] ?? NULL;
		$scheme = $origin["scheme"] ?? NULL;

		if(isset($origin["port"]))
			return ($host && $scheme) ? "$scheme://$host:{$origin["port"]}" : NULL;
		else
			return ($host && $scheme) ? "$scheme://$host" : NULL;
	}

	/**
	 * Fetches the host of a given request
	 *
	 * @param Request $request
	 * @param string|NULL $host
	 * @param string|NULL $scheme
	 * @return string
	 */
	public static function getHostOfRequest(Request $request, string &$host = NULL, string &$scheme = NULL): ?string {
		$host = $request->headers->get("HOST");
		$scheme = $request->isSecure() ? 'https' : 'http';
		return ($host && $scheme) ? "$scheme://$host" : NULL;
	}

	/**
	 * Checks, if a given host is registered
	 *
	 * @param $hostName
	 * @return bool
	 */
	public static function isRegistered($hostName) {
		return isset(self::$hosts[$hostName]);
	}
}