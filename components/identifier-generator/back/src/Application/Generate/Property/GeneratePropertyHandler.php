<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;

interface GeneratePropertyHandler
{
    public function supports(PropertyInterface $property): bool;

    public function __invoke(PropertyInterface $freeText, Target $target, string $prefix): string;
}
