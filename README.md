# Laasti/Stack

A middleware stack inspired by StackPHP.

You might be thinking "Why did you choose to develop a new package?".
StackPHP was relying on Symfony's Kernel Package only for its HttpKernelInterface.
I thought it was stupid to include a whole other package (with 3 dependencies of its own) just to use one Interface.
You might not agree with me in which case, I invite you to check out [StackPHP](http://stackphp.com/).

## Dependencies

Laasti/Stack requires only one composer package: [symfony/http-foundation](https://github.com/symfony/HttpFoundation).
I highly recommend it to handle request information such as Get, Post, Cookie, Session, Files.
It also provides a great way to generate responses in various formats: Html, Redirect, Json...