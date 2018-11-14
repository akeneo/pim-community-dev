<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorReferenceEntityByReferenceEntityCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindConnectorReferenceEntityByReferenceEntityCodeTest extends TestCase
{
    /** @var InMemoryFindConnectorReferenceEntityByReferenceEntityCodeTest */
    private $query;

    public function setup()
    {
        $this->query = new InMemoryFindConnectorReferenceEntityByReferenceEntityCode();
    }

    /**
     * @test
     */
    public function it_returns_null_when_finding_a_non_existent_record()
    {
        $result = ($this->query)(
            ReferenceEntityIdentifier::fromString('reference_entity')
        );

        Assert::assertNull($result);
    }

    /**
     * @test
     */
    public function it_returns_the_record_when_finding_an_existent_record()
    {
        $record = new ConnectorReferenceEntity();

        $this->query->save(
            ReferenceEntityIdentifier::fromString('reference_entity'),
            $record
        );

        $result = ($this->query)(
            ReferenceEntityIdentifier::fromString('reference_entity')
        );

        Assert::assertNotNull($result);
        Assert::assertSame(
            $record->normalize(),
            $result->normalize()
        );
    }
}
