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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use PHPUnit\Framework\TestCase;

class InMemoryFindAttributesDetailsTest extends TestCase
{
    /** @var InMemoryFindAttributesDetails */
    private $query;

    /** @var InMemoryFindActivatedLocales */
    private $activatedLocaleQuery;

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
        $this->query->save($this->createReferenceEntityDetails('designer', 'name'));
        $this->query->save($this->createReferenceEntityDetails('designer', 'description'));
        $this->query->save($this->createReferenceEntityDetails('manufacturer', 'name'));
        $this->query->save($this->createReferenceEntityDetails('manufacturer', 'description'));

        $manufacturerIdentifier = ReferenceEntityIdentifier::fromString('manufacturer');
        $designerIdentifier = ReferenceEntityIdentifier::fromString('manufacturer');
        $this->assertCount(2, ($this->query)($manufacturerIdentifier));
        $this->assertCount(2, ($this->query)($designerIdentifier));
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_are_no_attributes_for_the_given_reference_entity_identifier()
    {
        $manufacturerIdentifier = ReferenceEntityIdentifier::fromString('manufacturer');
        $this->assertEmpty(($this->query)($manufacturerIdentifier));
    }

    private function createReferenceEntityDetails(string $referenceEntityIdentifier, string $attributeCode): AttributeDetails
    {
        $textAttributeDetails = new AttributeDetails();
        $textAttributeDetails->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $textAttributeDetails->code = AttributeCode::fromString($attributeCode);

        return $textAttributeDetails;
    }
}
