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

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityIsLinkedToAtLeastOneProductAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryReferenceEntityIsLinkedToAtLeastOneProductAttributeTest extends TestCase
{
    /** @var InMemoryReferenceEntityIsLinkedToAtLeastOneProductAttribute */
    private $query;

    public function setUp(): void
    {
        parent::setUp();
        $referenceEntityattribute = new Attribute();
        $referenceEntityattribute->setCode('main_designer');
        $referenceEntityattribute->setType(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION);
        $referenceEntityattribute->setProperties([
            'reference_data_name' => 'designer'
        ]);

        $textareaAttribute = new Attribute();
        $textareaAttribute->setCode('description');
        $textareaAttribute->setType(AttributeTypes::TEXTAREA);

        $inMemoryAttributeRepository = new InMemoryAttributeRepository();
        $inMemoryAttributeRepository->save($referenceEntityattribute);
        $inMemoryAttributeRepository->save($textareaAttribute);

        $this->query = new InMemoryReferenceEntityIsLinkedToAtLeastOneProductAttribute($inMemoryAttributeRepository);
    }

    /**
     * @test
     */
    public function it_tells_if_a_reference_entity_is_linked_to_at_least_one_product_attribute()
    {
        $identifier = ReferenceEntityIdentifier::fromString('designer');
        $isLinked = ($this->query)($identifier);
        $this->assertTrue($isLinked);

        $identifier = ReferenceEntityIdentifier::fromString('brand');
        $isLinked = ($this->query)($identifier);
        $this->assertFalse($isLinked);
    }
}
