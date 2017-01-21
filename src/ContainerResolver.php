<?php

namespace Laasti\Stack;

use League\Container\ContainerInterface;

/**
 * Resolves Middleware using League\ContainerInterface
 */
class ContainerResolver implements ResolverInterface
{

    /**
     * The container
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retrieves a middleware from the container
     *
     * @param string $middleware
     * @return mixed
     */
    public function resolve($middleware)
    {
        //Not a string, should be an object
        if (!is_string($middleware)) {
            return $middleware;
        }
        return $this->container->get($middleware);
    }
}
