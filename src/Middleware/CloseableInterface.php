<?php

namespace Laasti\Stack\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Do something after the response is sent to the browser
 */
interface CloseableInterface
{

    /**
     * Output the response, then do other stuff before the connection is closed
     *
     * @param Request $request
     * @param Response $response
     */
    public function close(Request $request, Response $response);
}
