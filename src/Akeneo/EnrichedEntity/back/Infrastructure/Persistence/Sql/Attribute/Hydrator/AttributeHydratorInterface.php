<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

interface AttributeHydratorInterface
{
    public function supports(array $result): bool;

    public function hydrate(array $row): AbstractAttribute;
}
