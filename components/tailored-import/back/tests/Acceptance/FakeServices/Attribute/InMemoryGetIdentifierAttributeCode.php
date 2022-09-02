<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\FakeServices\Attribute;

use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\GetIdentifierAttributeCodeInterface;

class InMemoryGetIdentifierAttributeCode implements GetIdentifierAttributeCodeInterface
{
    public function execute(): string
    {
        return 'sku';
    }
}
