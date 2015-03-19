<?php

namespace Laasti\Stack\Test;

use Exception;
use Laasti\Stack;
use Laasti\Stack\MiddlewareInterface;
use Laasti\Stack\StackInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StackTest extends PHPUnit_Framework_TestCase
{

    public function testStackInterface()
    {
        $di = new \League\Container\Container();
        $stack = new Stack\ContainerStack($di);

        $this->assertTrue($stack instanceof StackInterface);
    }

    public function testStackResponse()
    {
        $di = new \League\Container\Container();
        $stack = new Stack\ContainerStack($di);

        $middleware = $this->getMock('Laasti\Stack\MiddlewareInterface');
        $middleware->expects($this->exactly(1))->method('handle')->will($this->returnValue(new Response));
        $di->add('MyMiddleware', $middleware);

        $stack->push('MyMiddleware');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $stack->execute(new Request));
    }

    /**
     * @expectedException  Laasti\Stack\StackException
     */
    public function testStackNoResponse()
    {
        $di = new \League\Container\Container();
        $stack = new Stack\ContainerStack($di);

        $middleware = $this->getMock('Laasti\Stack\MiddlewareInterface');
        $middleware->expects($this->exactly(1))->method('handle')->will($this->returnArgument(0));
        $di->add('MyMiddleware', $middleware);

        $stack->push('MyMiddleware');
        $stack->execute(new Request);
    }

    public function testStackUnshift()
    {
        $di = new \League\Container\Container();
        $stack = new Stack\ContainerStack($di);

        $middleware = $this->getMock('Laasti\Stack\MiddlewareInterface');
        $middleware->expects($this->any())->method('handle')->will($this->returnArgument(0));
        $di->add('MyPushMiddleware', $middleware);
        $stack->push('MyPushMiddleware');
        
        //This middleware should be called first
        $middleware = $this->getMock('Laasti\Stack\MiddlewareInterface');
        $middleware->expects($this->exactly(1))->method('handle')->will($this->returnValue(new Response));
        $di->add('MyUnshiftMiddleware', $middleware);
        $stack->unshift('MyUnshiftMiddleware');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $stack->execute(new Request));
    }


    public function testStackClose()
    {
        require_once 'TerminableMiddleware.php';
        $di = new \League\Container\Container();
        $stack = new Stack\ContainerStack($di);

        //Terminate should be called
        $middleware = $this->getMock('Laasti\Stack\Test\TerminableMiddleware');
        $middleware->expects($this->any())->method('handle')->will($this->returnValue(new Response));
        $middleware->expects($this->exactly(1))->method('terminate')->will($this->throwException(new Exception('My Test Exception')));
        $di->add('MyMiddleware', $middleware);
        $stack->push('MyMiddleware');

        try {
            $stack->execute(new Request);
            $stack->close(new Request, new Response);
        } catch(Exception $e) {
            $this->assertEquals('My Test Exception', $e->getMessage());
        }

    }

    public function testUnshiftStackClose()
    {
        require_once 'TerminableMiddleware.php';
        $di = new \League\Container\Container();
        $stack = new Stack\ContainerStack($di);

        //Terminate is called from last middleware to first middleware
        $middleware = $this->getMock('Laasti\Stack\Test\TerminableMiddleware');
        $middleware->expects($this->any())->method('handle')->will($this->returnValue(new Response));
        $middleware->expects($this->exactly(1))->method('terminate')->will($this->throwException(new Exception('My Test Exception Push')));
        $di->add('MyPushMiddleware', $middleware);
        $stack->push('MyPushMiddleware');

        $middleware2 = $this->getMock('Laasti\Stack\Test\TerminableMiddleware');
        $middleware2->expects($this->any())->method('handle')->will($this->returnValue(new Response));
        $middleware2->expects($this->any())->method('terminate')->will($this->throwException(new Exception('My Test Exception Unshift')));
        $di->add('MyUnshiftMiddleware', $middleware2);
        $stack->unshift('MyUnshiftMiddleware');

        try {
            $stack->execute(new Request);
            $stack->close(new Request, new Response);
        } catch(Exception $e) {
            $this->assertEquals('My Test Exception Push', $e->getMessage());
        }

    }

    public function testStackPassesArgsToMiddlewares() {
        require_once 'TerminableMiddleware.php';
        $di = new \League\Container\Container();
        $stack = new Stack\ContainerStack($di);
        $request = new Request;
        $response = new Response;

        $middleware = $this->getMock('Laasti\Stack\Test\TerminableMiddleware');
        $middleware->expects($this->exactly(1))->method('handle')->with($this->equalTo($request), $this->equalTo(2));
        $middleware->expects($this->exactly(1))->method('terminate')->with($this->equalTo($request),$this->equalTo($response), $this->equalTo(2));
        $di->add('MyPushMiddleware', $middleware);
        $stack->push('MyPushMiddleware', 2);

        //Valid middleware so the test does not crash
        $middleware = $this->getMock('Laasti\Stack\MiddlewareInterface');
        $middleware->expects($this->any())->method('handle')->will($this->returnValue(new Response));
        $di->add('MyPushMiddleware2', $middleware);
        $stack->push('MyPushMiddleware2');

        $stack->execute($request);
        $stack->close($request, $response);
    }

}
