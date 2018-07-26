<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Query;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;

interface ExistsAttribute
{
    public function withIdentifier(AttributeIdentifier $attributeIdentifier): bool;
    public function withEnrichedEntityIdentifierAndOrder(EnrichedEntityIdentifier $enrichedEntityIdentifier, AttributeOrder $order): bool;
}
