# Laasti/Stack

A middleware stack inspired by StackPHP.

You might be thinking "Why did you choose to develop a new package?".
StackPHP was relying on Symfony's Kernel Package only for its HttpKernelInterface.
I thought it was stupid to include a whole other package (with 3 dependencies of its own) just to use one Interface.
You might not agree with me in which case, I invite you to check out [StackPHP](http://stackphp.com/).

## Dependencies

Laasti/Stack requires only 2 composer packagew: [symfony/http-foundation](https://github.com/symfony/HttpFoundation) and [league/container](https://github.com/thephpleague/container).
I highly recommend it to handle request information such as Get, Post, Cookie, Session, Files.
It also provides a great way to generate responses in various formats: Html, Redirect, Json...

## How to use

```

//SimpleStack has no dependencies on a DI container and needs intances, so no lazy loading
$stack = new Laasti\Stack\SimpleStack();
$stack->push(new Laasti\Stack\MiddlewareInterface() /*, other args to pass to handle method */);

//ContainerStack relies on the awesomely simple League\Container
$di = new League\Container();
$stack = new Laasti\Stack\ContainerStack($di);
$stack->push('DependencyName', /*, other args to pass to handle method */);

You can add items to the stack during the stack execution.

//Run the application
//Throws a StackException if no response returned
$response = $stack->execute(new Symfony\Component\HttpFoundation\Request());

$stack->close(new Symfony\Component\HttpFoundation\Request(), $response);

```