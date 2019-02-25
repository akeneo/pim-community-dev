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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorReferenceEntityByReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryFindConnectorReferenceEntityByReferenceEntityIdentifierTest extends TestCase
{
    /** @var InMemoryFindConnectorReferenceEntityByReferenceEntityIdentifierTest */
    private $query;

    public function setUp(): void
    {
        $this->query = new InMemoryFindConnectorReferenceEntityByReferenceEntityIdentifier();
    }

    /**
     * @test
     */
    public function it_returns_null_when_finding_a_non_existent_reference_entity()
    {
        $result = ($this->query)(
            ReferenceEntityIdentifier::fromString('non_existent_reference_entity_identifier')
        );

        Assert::assertNull($result);
    }

    /**
     * @test
     */
    public function it_returns_the_reference_entity_when_finding_an_existing_reference_entity()
    {
        $referenceEntity = new ConnectorReferenceEntity(
            ReferenceEntityIdentifier::fromString('reference_entity_identifier'),
            LabelCollection::fromArray([]),
            Image::createEmpty()
        );

        $this->query->save(
            ReferenceEntityIdentifier::fromString('reference_entity_identifier'),
            $referenceEntity
        );

        $result = ($this->query)(
            ReferenceEntityIdentifier::fromString('reference_entity_identifier')
        );

        Assert::assertNotNull($result);
        Assert::assertEquals(
            $referenceEntity->normalize(),
            $referenceEntity->normalize()
        );
    }
}
