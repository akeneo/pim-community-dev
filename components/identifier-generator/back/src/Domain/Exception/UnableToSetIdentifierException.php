<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

class UnableToSetIdentifierException extends \Exception
{
    public function __construct(
        ErrorList $errorList
    ) {
        parent::__construct($errorList->__toString());
    }
}
