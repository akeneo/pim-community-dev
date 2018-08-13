<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Domain\Query\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;

/**
 * Query to determine if an Enriched Entity Attribute exists
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
interface AttributeExistsInterface
{
    public function withIdentifier(AttributeIdentifier $attributeIdentifier): bool;

    public function withEnrichedEntityIdentifierAndOrder(EnrichedEntityIdentifier $enrichedEntityIdentifier, AttributeOrder $order): bool;
}
