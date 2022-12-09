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

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocales;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindAttributesDetails;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use PHPUnit\Framework\TestCase;

class InMemoryFindAttributesDetailsTest extends TestCase
{
    private InMemoryFindAttributesDetails $query;
    private InMemoryFindActivatedLocales $activatedLocaleQuery;

    public function setUp(): void
    {
        $this->activatedLocaleQuery = new InMemoryFindActivatedLocales();
        $this->query = new InMemoryFindAttributesDetails($this->activatedLocaleQuery);
    }

    /**
     * @test
     */
    public function it_saves_multiple_attribute_details_from_different_reference_entity()
    {
        $this->query->save($this->createAttributeDetails('designer', 'name'));
        $this->query->save($this->createAttributeDetails('designer', 'description'));
        $this->query->save($this->createAttributeDetails('manufacturer', 'name'));
        $this->query->save($this->createAttributeDetails('manufacturer', 'description'));

        $manufacturerIdentifier = ReferenceEntityIdentifier::fromString('manufacturer');
        $designerIdentifier = ReferenceEntityIdentifier::fromString('manufacturer');
        $this->assertCount(2, $this->query->find($manufacturerIdentifier));
        $this->assertCount(2, $this->query->find($designerIdentifier));
    }

    /**
     * @test
     */
    public function it_can_find_an_attribute_by_its_identifier()
    {
        $nameDetails = $this->createAttributeDetails('designer', 'name');
        $descriptionDetails = $this->createAttributeDetails('manufacturer', 'description');

        $this->query->save($nameDetails);
        $this->query->save($descriptionDetails);

        $this->assertEquals($nameDetails, $this->query->findByIdentifier(
            AttributeIdentifier::fromString('name'),
        ));
        $this->assertEquals($descriptionDetails, $this->query->findByIdentifier(
            AttributeIdentifier::fromString('description'),
        ));
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_are_no_attributes_for_the_given_reference_entity_identifier()
    {
        $manufacturerIdentifier = ReferenceEntityIdentifier::fromString('manufacturer');
        $this->assertEmpty($this->query->find($manufacturerIdentifier));
    }

    private function createAttributeDetails(string $referenceEntityIdentifier, string $attributeCode): AttributeDetails
    {
        $textAttributeDetails = new AttributeDetails();
        $textAttributeDetails->identifier = $attributeCode;
        $textAttributeDetails->referenceEntityIdentifier = $referenceEntityIdentifier;
        $textAttributeDetails->code = $attributeCode;
        $textAttributeDetails->labels = [];

        return $textAttributeDetails;
    }
}
