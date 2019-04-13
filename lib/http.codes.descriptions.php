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

return [
	400 => 'This response means that server could not understand the request "%s".',
	401 => 'Authentication is needed to get requested response. This is similar to 403, but in this case, authentication is possible.',
	402 => 'This response code is reserved for future use. Initial aim for creating this code was using it for digital payment systems however this is not used currently.',
	403 => "Client does not have access rights to the content so server is rejecting to give proper response.<br>(Requested URI: %s)",
	404 => 'Server can not find requested resource %s. This response code probably is most famous one due to its frequency to occur in web.',
	405 => 'The request method is known by the server but has been disabled and cannot be used. The two mandatory methods, GET and HEAD, must never be disabled and should not return this error code.',
	406 => 'This response is sent when the web server, after performing server-driven content negotiation, doesn\'t find any content following the criteria "%s" given by the user agent.',
	407 => 'This is similar to 401 but authentication is needed to be done by a proxy.',
	408 => 'This response is sent on an idle connection by some servers, even without any previous request by the client. It means that the server would like to shut down this unused connection. This response is used much more since some browsers, like Chrome or IE9, use HTTP preconnection mechanisms to speed up surfing (see bug 881804, which tracks the future implementation of such a mechanism in Firefox). Also note that some servers merely shut down the connection without sending this message.',
	409 => 'This response would be sent when a request conflict with current state of server. %s',
	410 => 'This response would be sent when requested content has been deleted from server.',
	411 => 'Server rejected the request because the Content-Length header field is not defined and the server requires it.',
	412 => 'The client has indicated preconditions in its headers which the server does not meet.',
	413 => 'Request entity is larger than limits defined by server; the server might close the connection or return an Retry-After header field.',
	414 => 'The URI requested by the client is longer than the server is willing to interpret.',
	415 => 'The media format of the requested data is not supported by the server, so the server is rejecting the request.',
	416 => 'The range specified by the Range header field in the request can\'t be fulfilled; it\'s possible that the range is outside the size of the target URI\'s data.',
	417 => 'This response code means the expectation indicated by the Expect request header field can\'t be met by the server.',
	418 => 'Any attempt to brew coffee with a teapot should result in the error code "418 I\'m a teapot". The resulting entity body MAY be short and stout.',
	421 => 'The request was directed at a server that is not able to produce a response. This can be sent by a server that is not configured to produce responses for the combination of scheme and authority that are included in the request URI.',
	426 => 'The server refuses to perform the request using the current protocol but might be willing to do so after the client upgrades to a different protocol. The server MUST send an Upgrade header field in a 426 response to indicate the required protocol(s) (Section 6.7 of [RFC7230]).',
	428 => 'The origin server requires the request to be conditional. Intended to prevent \\"the \'lost update\' problem, where a client GETs a resource\'s state, modifies it, and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict."',
	429 => 'The user has sent too many requests in a given amount of time ("rate limiting").',
	431 => 'The server is unwilling to process the request because its header fields are too large. The request MAY be resubmitted after reducing the size of the request header fields.',
	500 => 'The server has encountered a situation it doesn\'t know how to handle. Please contact the server\'s administrator.',
	501 => 'The request method is not supported by the server and cannot be handled. The only methods that servers are required to support (and therefore that must not return this code) are GET and HEAD.',
	502 => 'This error response means that the server, while working as a gateway to get a response needed to handle the request, got an invalid response.',
	503 => 'The server is not ready to handle the request. Common causes are a server that is down for maintenance or that is overloaded. Note that together with this response, a user-friendly page explaining the problem should be sent. This responses should be used for temporary conditions and the Retry-After: HTTP header should, if possible, contain the estimated time before the recovery of the service. The webmaster must also take care about the caching-related headers that are sent along with this response, as these temporary condition responses should usually not be cached.',
	504 => 'This error response is given when the server is acting as a gateway and cannot get a response in time.',
	505 => 'The HTTP version used in the request is not supported by the server.',
	506 => 'The server has an internal configuration error: transparent content negotiation for the request results in a circular reference.',
	507 => 'The server has an internal configuration error: the chosen variant resource is configured to engage in transparent content negotiation itself, and is therefore not a proper end point in the negotiation process.',
	511 => 'The 511 status code indicates that the client needs to authenticate to gain network access.',
];