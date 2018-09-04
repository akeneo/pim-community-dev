<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\HydratorInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;

interface AttributeHydratorInterface
{
    public function supports(array $result): bool;

    public function hydrate(AbstractPlatform $platform, array $row): AbstractAttribute;
}
