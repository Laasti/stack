<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Stack\Test;

use Laasti\Services\MiddlewareInterface;
use Laasti\Services\MiddlewareTerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of TerminableMiddleware
 *
 * @author Sonia
 */
class TerminableMiddleware implements MiddlewareInterface, MiddlewareTerminableInterface
{
    /**
     *
     * @param Request $request
     * @return Request|Response
     */
    public function handle(Request $request) {
        return $request;
    }
    
    public function terminate(Request $request, Response $response) {
        //Does nothing
    }
}
