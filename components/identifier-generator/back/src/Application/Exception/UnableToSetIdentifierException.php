<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

class UnableToSetIdentifierException extends \Exception
{
    public function __construct(
        private string $identifier,
        private string $target,
        private ErrorList $errorList
    ) {
        parent::__construct(\sprintf(
            "Your product has been saved but your identifier could not be generated:\n%s",
            $errorList->__toString()
        ));
    }

    /**
     * @return array<string, string>
     */
    public function getInvalidData(): array
    {
        return [$this->target => $this->identifier];
    }

    public function getErrors(): ErrorList
    {
        return $this->errorList;
    }
}
