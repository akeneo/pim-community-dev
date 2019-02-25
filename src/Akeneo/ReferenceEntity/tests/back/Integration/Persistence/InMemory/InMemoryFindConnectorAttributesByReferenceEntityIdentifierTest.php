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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorAttributesByReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryFindConnectorAttributesByReferenceEntityIdentifierTest extends TestCase
{
    /** @var InMemoryFindConnectorAttributesByReferenceEntityIdentifier */
    private $query;

    public function setUp(): void
    {
        parent::setUp();
        $this->query = new InMemoryFindConnectorAttributesByReferenceEntityIdentifier();
    }

    /**
     * @test
     */
    public function it_returns_null_when_finding_a_non_existent_reference_entity()
    {
        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            'text',
            AttributeValuePerLocale::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeIsRequired::fromBoolean(true),
            []
        );

        $result = ($this->query)(
            ReferenceEntityIdentifier::fromString('non_existent_reference_entity_identifier'),
            $connectorAttribute
        );

        Assert::assertEmpty($result);
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_when_finding_an_existing_reference_entity()
    {
        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            'text',
            AttributeValuePerLocale::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeIsRequired::fromBoolean(true),
            []
        );

        $this->query->save(
            ReferenceEntityIdentifier::fromString('existent_reference_entity_identifier'),
            $connectorAttribute
        );

        $results = ($this->query)(
            ReferenceEntityIdentifier::fromString('existent_reference_entity_identifier'),
            $connectorAttribute
        );

        Assert::assertNotNull($results);
        Assert::assertSame([
            $connectorAttribute
        ], $results);
    }
}
