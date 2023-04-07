<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception;

class CouldNotFindIdentifierGeneratorException extends \LogicException
{
    public function __construct(string $identifierGenerator)
    {
        parent::__construct(\sprintf('Could not find identifier generator %s', $identifierGenerator));
    }
}
