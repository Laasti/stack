<?php

namespace Laasti\Stack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface used by middleware stacks
 */
interface StackInterface
{

    /**
     * Prepend a middleware to the stack
     * @param mixed
     */
    public function unshift($middleware);

    /**
     * Append a middleware to the stack
     * @param mixed
     */
    public function push($middleware);

    /**
     * Goes through the stack of middlewares to generate a Response
     *
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request);
}
