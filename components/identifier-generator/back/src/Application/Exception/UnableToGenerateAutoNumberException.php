<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

class UnableToGenerateAutoNumberException extends UnableToSetIdentifierException
{
    public function __construct(
        string $identifier,
        string $target,
    ) {
        parent::__construct($identifier, $target, new ErrorList([
            new Error('The Auto number could not be generated.'),
        ]));
    }
}
