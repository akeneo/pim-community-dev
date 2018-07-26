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

namespace Akeneo\EnrichedEntity\tests\back\Common;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\ExistsAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryExistsAttribute implements ExistsAttribute
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function withIdentifier(AttributeIdentifier $attributeIdentifier): bool
    {
        $key = $this->attributeRepository->getKey($attributeIdentifier);
        $attributes = $this->attributeRepository->getAttributes();

        return isset($attributes[$key]);
    }

    public function withEnrichedEntityIdentifierAndOrder(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeOrder $order
    ): bool
    {
        $attributes = $this->attributeRepository->getAttributes();
        foreach ($attributes as $attribute) {
            if ((string) $enrichedEntityIdentifier === (string) $attribute->getEnrichedEntityIdentifier() &&
                $attribute->hasOrder($order)
            ) {
                return true;
            }
        }

        return false;
    }
}
