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

namespace Akeneo\EnrichedEntity\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityIsLinkedToAtLeastOneProductAttributeInterface;
use Akeneo\Pim\EnrichedEntity\Component\AttributeType\EnrichedEntityCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository as InMemoryProductAttributeRepository;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryEnrichedEntityIsLinkedToAtLeastOneProductAttribute implements EnrichedEntityIsLinkedToAtLeastOneProductAttributeInterface
{
    /** @var InMemoryProductAttributeRepository */
    private $inMemoryAttributeRepository;

    public function __construct(InMemoryProductAttributeRepository $inMemoryAttributeRepository)
    {
        $this->inMemoryAttributeRepository = $inMemoryAttributeRepository;
    }

    public function __invoke(EnrichedEntityIdentifier $identifier): bool
    {
        $attributes = $this->inMemoryAttributeRepository->findBy([
            'attributeType' => EnrichedEntityCollectionType::ENRICHED_ENTITY_COLLECTION,
        ]);

        $linkedEntities = [];
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $linkedEntities[] = $attribute->getProperty('reference_data_name');
        }

        return in_array((string) $identifier, array_filter(array_unique($linkedEntities)));
    }
}
