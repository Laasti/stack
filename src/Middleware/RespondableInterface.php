<?php

namespace Laasti\Stack\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Filters the response before it is sent to the browser
 */
interface RespondableInterface
{

    /**
     * Filters the response before it is sent to the browser
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function respond(Request $request, Response $response);
}
