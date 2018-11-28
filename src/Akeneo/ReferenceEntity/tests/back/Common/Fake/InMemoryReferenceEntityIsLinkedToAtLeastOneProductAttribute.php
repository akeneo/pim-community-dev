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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityIsLinkedToAtLeastOneProductAttributeInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository as InMemoryProductAttributeRepository;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryReferenceEntityIsLinkedToAtLeastOneProductAttribute implements ReferenceEntityIsLinkedToAtLeastOneProductAttributeInterface
{
    /** @var InMemoryProductAttributeRepository */
    private $inMemoryAttributeRepository;

    public function __construct(InMemoryProductAttributeRepository $inMemoryAttributeRepository)
    {
        $this->inMemoryAttributeRepository = $inMemoryAttributeRepository;
    }

    public function __invoke(ReferenceEntityIdentifier $identifier): bool
    {
        $attributes = $this->inMemoryAttributeRepository->findBy([
            'attributeType' => ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION,
        ]);

        $linkedEntities = [];
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $linkedEntities[] = $attribute->getProperty('reference_data_name');
        }

        return in_array((string) $identifier, array_filter(array_unique($linkedEntities)));
    }
}
