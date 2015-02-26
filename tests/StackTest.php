<?php

namespace Laasti\Test;

use Laasti\Stack;
use Laasti\Services\StackInterface;
use Laasti\Services\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class StackTest extends \PHPUnit_Framework_TestCase
{

    public function testStackInterface()
    {
        $stack = new Stack\Stack();

        $this->assertTrue($stack instanceof StackInterface);
    }

    public function testStackResponse()
    {
        $stack = new Stack\Stack();

        $middleware = $this->getMock('Laasti\Services\MiddlewareInterface');
        $middleware->expects($this->exactly(1))->method('handle')->will($this->returnValue(new Response));
        $this->assertTrue($middleware instanceof MiddlewareInterface);

        $stack->push($middleware);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $stack->execute(new Request));
    }

    /**
     * @expectedException \Laasti\Stack\StackException
     */
    public function testStackNoResponse()
    {
        $stack = new Stack\Stack();

        $middleware = $this->getMock('Laasti\Services\MiddlewareInterface');
        $middleware->expects($this->exactly(1))->method('handle')->will($this->returnArgument(0));
        $this->assertTrue($middleware instanceof MiddlewareInterface);

        $stack->push($middleware);
        $stack->execute(new Request);
    }

    public function testStackUnshift()
    {

        $stack = new Stack\Stack();

        $middleware = $this->getMock('Laasti\Services\MiddlewareInterface');
        $middleware->expects($this->any())->method('handle')->will($this->returnArgument(0));
        $stack->push($middleware);
        $middleware = $this->getMock('Laasti\Services\MiddlewareInterface');
        $middleware->expects($this->exactly(1))->method('handle')->will($this->returnValue(new Response));
        $stack->unshift($middleware);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $stack->execute(new Request));
    }


    public function testStackClose()
    {
        $stack = new Stack\Stack();

        $middleware = $this->getMock('Laasti\Services\MiddlewareInterface');
        $middleware->expects($this->any())->method('handle')->will($this->returnValue(new Response));
        $middleware->expects($this->exactly(1))->method('terminate')->will($this->throwException(new \Exception('My Test Exception')));
        $stack->push($middleware);

        try {
            $stack->close(new Request, new Response);
        } catch(\Exception $e) {
            $this->assertEquals('My Test Exception', $e->getMessage());
        }

    }

    public function testUnshiftStackClose()
    {
        $stack = new Stack\Stack();

        $middleware = $this->getMock('Laasti\Services\MiddlewareInterface');
        $middleware->expects($this->any())->method('handle')->will($this->returnValue(new Response));
        $middleware->expects($this->exactly(1))->method('terminate')->will($this->throwException(new \Exception('My Test Exception Push')));
        $stack->push($middleware);

        $middleware2 = $this->getMock('Laasti\Services\MiddlewareInterface');
        $middleware2->expects($this->any())->method('handle')->will($this->returnValue(new Response));
        $middleware2->expects($this->any())->method('terminate')->will($this->throwException(new \Exception('My Test Exception Unshift')));
        $stack->unshift($middleware2);

        try {
            $stack->close(new Request, new Response);
        } catch(\Exception $e) {
            $this->assertEquals('My Test Exception Push', $e->getMessage());
        }

    }


}
