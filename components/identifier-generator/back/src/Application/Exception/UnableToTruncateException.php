<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

class UnableToTruncateException extends UnableToSetIdentifierException
{
    public function __construct(
        string $identifier,
        string $target,
        string $code,
    ) {
        parent::__construct($identifier, $target, new ErrorList([
            new Error(\sprintf('The code does not have enough characters and can not be truncated: "%s".', $code)),
        ]));
    }
}
