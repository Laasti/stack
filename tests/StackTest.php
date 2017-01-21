<?php

namespace Laasti\Stack\Test;

use InvalidArgumentException;
use Laasti\Stack;
use Laasti\Stack\Middleware\PrepareableInterface;
use Laasti\Stack\StackException;
use Laasti\Stack\StackInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StackTest extends PHPUnit_Framework_TestCase
{

    public function testStackInterface()
    {
        $stack = new Stack\Stack();

        $this->assertTrue($stack instanceof StackInterface);
    }

    public function testStackResponse()
    {
        $stack = new Stack\Stack();
        $responseMessage = 'My super response';
        $middleware = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $this->expectOutputString($responseMessage);
        $middleware->expects($this->exactly(1))->method('prepare')->will($this->returnValue(new Response($responseMessage)));

        $this->assertTrue($middleware instanceof PrepareableInterface);

        $stack->push($middleware);

        $stack->execute(new Request);
    }

    public function testStackNoResponse()
    {
        $stack = new Stack\Stack();
        $middleware = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $middleware->expects($this->exactly(1))->method('prepare')->will($this->returnValue(new Request));
        $stack->push($middleware);

        try {
            $stack->execute(new Request);
        } catch (StackException $e) {
            $this->assertInstanceOf('Laasti\Stack\StackException', $e);
            return;
        }

        $this->fail();
    }

    public function testEmptyStack()
    {
        $stack = new Stack\Stack();

        try {
            $stack->execute(new Request);
        } catch (StackException $e) {
            $this->assertInstanceOf('Laasti\Stack\StackException', $e);
            return;
        }

        $this->fail();
    }

    public function testInvalidPushMiddleware()
    {
        $stack = new Stack\Stack();
        $middleware = $this->getMockBuilder('MyFakeMiddleware')->getMock();
        try {
            $stack->push($middleware);
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail();
    }

    public function testInvalidUnshiftMiddleware()
    {
        $stack = new Stack\Stack();
        $middleware = $this->getMockBuilder('MyFakeMiddleware')->getMock();
        try {
            $stack->unshift($middleware);
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
            return;
        }

        $this->fail();
    }

    public function testUnshiftMiddleware()
    {
        $stack = new Stack\Stack();
        $middleware = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $middleware2 = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $responseMessage = 'Test response';
        $this->expectOutputString($responseMessage);
        $middleware->expects($this->exactly(0))->method('prepare')->will($this->returnValue(new Request));
        $middleware2->expects($this->exactly(1))->method('prepare')->will($this->returnValue(new Response($responseMessage)));

        $stack->push($middleware);
        $stack->unshift($middleware2);

        $stack->execute(new Request);
    }

    public function testMiddlewareArguments()
    {
        $stack = new Stack\Stack();

        $responseMessage = 'Test response';

        $this->expectOutputString($responseMessage);

        $middleware = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $middleware2 = $this->getMock('Laasti\Stack\Middleware\RespondableInterface');
        $middleware3 = $this->getMock('Laasti\Stack\Middleware\CloseableInterface');

        $middleware->expects($this->exactly(1))->method('prepare')->with($this->callback(function ($request) {
            return $request instanceof Request;
        }), 1)->will($this->returnValue(new Response($responseMessage)));
        $middleware2->expects($this->exactly(1))->method('respond')->with(
            $this->callback(function ($request) {
                return $request instanceof Request;
            }),
            $this->callback(function ($response) {
                return $response instanceof Response;
            }),
            2
        )->will($this->returnValue(new Response($responseMessage)));
        $middleware3->expects($this->exactly(1))->method('close')->with(
            $this->callback(function ($request) {
                return $request instanceof Request;
            }),
            $this->callback(function ($response) {
                return $response instanceof Response;
            }),
            3
        )->will($this->returnValue(new Response($responseMessage)));

        $stack->push($middleware, 1);
        $stack->push($middleware2, 2);
        $stack->push($middleware3, 3);

        $stack->execute(new Request);
    }

    public function testStackPhases()
    {
        $stack = new Stack\Stack();

        $responseMessage = 'Test response';

        $this->expectOutputString($responseMessage);

        $middleware = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $middleware2 = $this->getMock('Laasti\Stack\Middleware\RespondableInterface');
        $middleware3 = $this->getMock('Laasti\Stack\Middleware\CloseableInterface');

        $middleware->expects($this->exactly(1))->method('prepare')->with($this->callback(function ($request) {
            return count(func_get_args()) === 1 && $request instanceof Request;
        }))->will($this->returnValue(new Response($responseMessage)));
        $middleware2->expects($this->exactly(1))->method('respond')->with(
            $this->callback(function ($request) {
                return $request instanceof Request;
            }),
            $this->callback(function ($response) {
                return $response instanceof Response;
            })
        )->will($this->returnValue(new Response($responseMessage)));
        $middleware3->expects($this->exactly(1))->method('close')->with(
            $this->callback(function ($request) {
                return $request instanceof Request;
            }),
            $this->callback(function ($response) {
                return $response instanceof Response;
            })
        )->will($this->returnValue(new Response($responseMessage)));

        $stack->push($middleware);
        $stack->push($middleware2);
        $stack->push($middleware3);

        $stack->execute(new Request);
    }

    public function testRespondOverwritePrepare()
    {
        $stack = new Stack\Stack();

        $prepareMessage = 'Test prepare';
        $responseMessage = 'Test response';

        $this->expectOutputString($responseMessage);

        $middleware = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $middleware2 = $this->getMock('Laasti\Stack\Middleware\RespondableInterface');

        $middleware->expects($this->exactly(1))->method('prepare')->will($this->returnValue(new Response($prepareMessage)));
        $middleware2->expects($this->exactly(1))->method('respond')->will($this->returnValue(new Response($responseMessage)));

        $stack->push($middleware);
        $stack->push($middleware2);

        $stack->execute(new Request);
    }

    public function testContainerResolver()
    {
        $container = new \League\Container\Container();
        $middleware = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $middleware2 = $this->getMock('Laasti\Stack\Middleware\PrepareableInterface');
        $resolver = new Stack\ContainerResolver($container, $middleware);
        $container->add('MyMiddleware', $middleware);
        $stack = new Stack\Stack($resolver);

        //Use key from container
        $stack->push('MyMiddleware');
        //Should still work
        $stack->push($middleware2);

        $responseMessage = 'Test response';

        $this->expectOutputString($responseMessage);
        $middleware->expects($this->exactly(1))->method('prepare')->will($this->returnValue(new Response($responseMessage)));
        $middleware2->expects($this->exactly(0))->method('prepare')->will($this->returnValue(new Response($responseMessage)));

        $stack->execute(new Request);
    }
}
