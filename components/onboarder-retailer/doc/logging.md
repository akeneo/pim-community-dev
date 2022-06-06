# Logging

This page describes the best-practices to follow when adding logs in the Onboarder Serenity application.

## Adding Logs In The Back-End

### The Basics

In Onboarder Serenity back-end, we log through [the Symfony logger](https://symfony.com/doc/current/logging.html),
a [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger, backed-up by [Monolog](https://seldaek.github.io/monolog/)
which provides additional functionalities, like separation of logs by channels, more configuration options, etc…

To use the logger, we simply need to inject the `Psr\Log\LoggerInterface` service, as you can see in the following example.

```php
<?php

declare(strict_types=1);

namespace Foo;

use Psr\Log\LoggerInterface;

class Bar
{
    public function __construct(private LoggerInterface $logger)
    {}

    public function __invoke(): void
    {
        $this->logger->log('A very useful log!');
    }
}
```

### Where to log

You can use the logger in any service of the `Infrastructure`/`Application` namespaces.

What you can do in the `Domain` namespace is more restricted. Basically, the domain should not rely on anything but
itself. This means we cannot use the logger in it, as it is an external dependency.

### Log Levels

The [PSR-3](https://www.php-fig.org/psr/psr-3/) describes several [level of logs](https://github.com/php-fig/log/blob/master/src/LogLevel.php).
You can find a description of each level and when to use them in the [LoggerInterface](https://github.com/php-fig/log/blob/master/src/LoggerInterface.php).
The interface provides a dedicated method for each level.

In Onboarder Serenity, we mostly use 4 levels:
- `debug`: Use it to log any data that can be useful while developing and testing. Don't be shy with that one, as those
  logs will not be generated in production (we filter and keep only `info` and higher levels in the Symfony `prod`
  environment used in production).
- `info`: Log anything that may be useful to better understand what the application does in production. For example, it
  can be used to add metrics or during maintenance.
- `warning`: This is used to log weird, but anticipated behaviors. The warning
  also bears the concept of "it's OK, but it would be better if it was not happening".
- `error`: This is clearly something that should not happen, and it usually requires a bug fix, or at least a close
  monitoring.

### Additional Data

The PSR-3 logger allows to add a "context" to a log. It is an associative array in which we can provide any relevant
information to complete the message of the log. This is an optional argument that can be passed to any "level" methods.

You can add any other relevant data to the log's context, but beware that the whole message+context will become a single
JSON log entry in Datadog, and that this log entry cannot exceed 256 kB.
If more, it will be automatically split in several pieces. If your log is more than 1 MB, it will then be truncated,
and you will lose a part of it.

Logging example with context:

```php
$this->logger->info(
    'A log message',
    [
        'supplier_code' => $supplierCode,
    ],
);
```

### Logging an exception

Exception are a special case. Receiving an exception in your code can be a normal behavior.
However, it can be that we try to catch an exception "just in case", because it can be thrown, but it shouldn't if the
application behaves normally. In this case, we will log it as an error, and add 2 special keys in the log context:
`message` and `exception`. This is because we are using the Monolog `include_stacktraces` parameter. This allows us to
automatically log not only the current exception, but also its previous one, and the previous one of the previous one,
etc… This provides us the whole stack trace of the error in our log.

As a summary, when you want to log an exception, it should be done as follows:
```php
try {
    $someService->thatIsDoingSomething();
} catch (\Throwable $anExceptionThatShouldNotHaveBeenThrown) {
    $this->logger->error(
        'A message that explains what happened that should not have',   // This can be a custom message if the error is anticipated,
        [                                                               //  or just the caught exception message
            'exception' => $anExceptionThatShouldNotHaveBeenThrown,             // It is mandatory to have both parameters
            'message' => $anExceptionThatShouldNotHaveBeenThrown->getMessage(), // for monolog to build the full stacktrace.
        ],
    );
}
```
