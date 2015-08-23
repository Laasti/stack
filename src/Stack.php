<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Stack;

use InvalidArgumentException;
use Laasti\Stack\Middleware\CloseableInterface;
use Laasti\Stack\Middleware\PrepareableInterface;
use Laasti\Stack\Middleware\RespondableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of Stack
 *
 * @author Sonia
 */
class Stack implements StackInterface
{

    /**
     * Contains the prepareable middlewares
     * @var array
     */
    protected $prepareableMiddlewares = [];

    /**
     * Contains the respondable middlewares
     * @var array
     */
    protected $respondableMiddlewares = [];

    /**
     * Contains the closeable middlewares
     * @var array
     */
    protected $closeableMiddlewares = [];

    /**
     * Middleware Instance Resolver
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * Constructor
     * 
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     * Adds the middleware at the beginning of aggregates
     * You can pass additional arguments to the method. They will be used when calling handle/close methods.
     *
     * @param PrepareableInterface|RespondableInterface|CloseableInterface $middleware
     * @return Stack
     */
    public function unshift($middleware)
    {
        $this->addMiddleware($middleware, func_get_args(), false);
        return $this;
    }

    /**
     * Adds the middleware at the end of the aggregates
     * You can pass additional arguments to the method. They will be used when calling handle/close methods.
     *
     * @param PrepareableInterface|RespondableInterface|CloseableInterface $middleware
     * @return Stack
     */
    public function push($middleware)
    {
        $this->addMiddleware($middleware, func_get_args(), true);

        return $this;
    }

    /**
     * Loops through all registered middlewares until a response is returned.
     *
     * @throws StackException When no response is returned
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request)
    {

        $phases = ['prepare', 'respond', 'close'];
        $response = null;

        foreach ($phases as $phase) {

            $key = 0;

            if ($phase === 'respond' && is_null($response)) {
                throw new StackException;
            } else if ($phase === 'close' && $key === 0) {
                $response->send();
            }

            if ($phase === 'prepare' && count($this->{$phase . 'ableMiddlewares'}) === 0) {
                throw new StackException('You must at least add one prepareableMiddleware that generates a Response.');
            } else if (count($this->{$phase . 'ableMiddlewares'}) === 0) {
                continue;
            }

            $next_middleware_spec = $this->{$phase . 'ableMiddlewares'}[$key];

            //Use a while so that middlewares can add other middlewares to execute
            while ($next_middleware_spec) {
                //Get the middleware
                $middleware = array_shift($next_middleware_spec);

                if (!is_null($response)) {
                    array_unshift($next_middleware_spec, $response);
                }

                //Put request as first parameter for the middleware
                array_unshift($next_middleware_spec, $request);

                $return = call_user_func_array([$middleware, $phase], $next_middleware_spec);

                if ($phase === 'prepare' && $return instanceof Response) {
                    $response = $return;
                    break;
                } else if ($phase === 'respond') {
                    //Reassign response in case it was recreated during that phase
                    $response = $return;
                }
                $key++;
                $next_middleware_spec = isset($this->{$phase . 'ableMiddlewares'}[$key]) ? $this->{$phase . 'ableMiddlewares'}[$key] : false;
            }
        }
    }

    /**
     * Adds a middleware to aggregates
     * @param mixed $middleware
     * @param array $args
     * @param bool $push Whether to push the middleware, false, or unshift, true
     * @throws InvalidArgumentException
     */
    private function addMiddleware($middleware, $args, $push = true)
    {
        $instance = $this->resolve($middleware);

        $types = $this->getTypes($instance);

        if (!count($types)) {
            throw new InvalidArgumentException('The first argument must be an instance of PrepareableInterface, RespondableInterface or CloseableInterface.');
        }

        //Replace middleware definition with instance
        $args[0] = $instance;

        foreach ($types as $type) {
            if ($push) {
                array_push($this->{$type . 'Middlewares'}, $args);
            } else {
                array_unshift($this->{$type . 'Middlewares'}, $args);
            }
        }

    }

    /**
     * Returns the applicable types for the middleware
     * @param mixed $middleware
     * @return array
     */
    private function getTypes($middleware)
    {
        $types = [];

        if ($middleware instanceof PrepareableInterface) {
            $types[] = 'prepareable';
        }
        if ($middleware instanceof RespondableInterface) {
            $types[] = 'respondable';
        }
        if ($middleware instanceof CloseableInterface) {
            $types[] = 'closeable';
        }

        return $types;
    }

    /**
     * Resolve the argument to an actual middleware instance
     * @param mixed $definition
     * @return mixed
     */
    private function resolve($definition)
    {
        return !is_null($this->resolver) ? $this->resolver->resolve($definition) : $definition;
    }

}
