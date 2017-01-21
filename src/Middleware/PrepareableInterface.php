<?php

namespace Laasti\Stack\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prepare the request for the respond phase
 */
interface PrepareableInterface
{

    /**
     * Prepare the request for the respond phase.
     * Return a Response to skip to respond phase.
     *
     * @param Request $request
     * @return Request|Response
     */
    public function prepare(Request $request);
}
