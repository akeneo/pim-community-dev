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

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\TextAttributeDetails;
use Akeneo\EnrichedEntity\tests\back\Common\Fake\InMemoryFindAttributesDetails;
use PHPUnit\Framework\TestCase;

class InMemoryFindAttributesDetailsTest extends TestCase
{
    /** @var InMemoryFindAttributesDetails */
    private $query;

    public function setup()
    {
        $this->query = new InMemoryFindAttributesDetails();
    }

    /**
     * @test
     */
    public function it_saves_multiple_attribute_details_from_different_enriched_entity()
    {
        $this->query->save($this->createEnrichedEntityDetails('designer', 'name'));
        $this->query->save($this->createEnrichedEntityDetails('designer', 'description'));
        $this->query->save($this->createEnrichedEntityDetails('manufacturer', 'name'));
        $this->query->save($this->createEnrichedEntityDetails('manufacturer', 'description'));

        $manufacturerIdentifier = EnrichedEntityIdentifier::fromString('manufacturer');
        $designerIdentifier = EnrichedEntityIdentifier::fromString('manufacturer');
        $this->assertCount(2, ($this->query)($manufacturerIdentifier));
        $this->assertCount(2, ($this->query)($designerIdentifier));
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_are_no_attributes_for_the_given_enriched_entity_identifier()
    {
        $manufacturerIdentifier = EnrichedEntityIdentifier::fromString('manufacturer');
        $this->assertEmpty(($this->query)($manufacturerIdentifier));
    }

    private function createEnrichedEntityDetails(string $enrichedEntityIdentifier, string $attributeCode): AbstractAttributeDetails
    {
        $textAttributeDetails = new TextAttributeDetails();
        $textAttributeDetails->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
        $textAttributeDetails->code = AttributeCode::fromString($attributeCode);

        return $textAttributeDetails;
    }
}
