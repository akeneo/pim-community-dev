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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\InMemory;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\tests\back\Common\Fake\InMemoryEnrichedEntityIsLinkedToAtLeastOneProductAttribute;
use Akeneo\Pim\EnrichedEntity\Component\AttributeType\EnrichedEntityCollectionType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryEnrichedEntityIsLinkedToAtLeastOneProductAttributeTest extends TestCase
{
    /** @var InMemoryEnrichedEntityIsLinkedToAtLeastOneProductAttribute */
    private $query;

    public function setup()
    {
        $enrichedEntityattribute = new Attribute();
        $enrichedEntityattribute->setCode('main_designer');
        $enrichedEntityattribute->setType(EnrichedEntityCollectionType::ENRICHED_ENTITY_COLLECTION);
        $enrichedEntityattribute->setProperties([
            'reference_data_name' => 'designer'
        ]);

        $textareaAttribute = new Attribute();
        $textareaAttribute->setCode('description');
        $textareaAttribute->setType(AttributeTypes::TEXTAREA);

        $inMemoryAttributeRepository = new InMemoryAttributeRepository();
        $inMemoryAttributeRepository->save($enrichedEntityattribute);
        $inMemoryAttributeRepository->save($textareaAttribute);

        $this->query = new InMemoryEnrichedEntityIsLinkedToAtLeastOneProductAttribute($inMemoryAttributeRepository);
    }

    /**
     * @test
     */
    public function it_tells_if_an_enriched_entity_is_linked_to_at_least_one_product_attribute()
    {
        $identifier = EnrichedEntityIdentifier::fromString('designer');
        $isLinked = ($this->query)($identifier);
        $this->assertTrue($isLinked);

        $identifier = EnrichedEntityIdentifier::fromString('brand');
        $isLinked = ($this->query)($identifier);
        $this->assertFalse($isLinked);
    }
}
