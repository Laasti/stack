<?php

namespace Laasti\Stack;

/**
 * Resolves middlewares to instances
 */
interface ResolverInterface
{

    /**
     * Returns an instance of the middleware, or throws an exception
     * @param mixed $middleware
     */
    public function resolve($middleware);
}
