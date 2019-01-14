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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorRecordByReferenceEntityAndCode;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryFindConnectorRecordTest extends TestCase
{
    /** @var InMemoryFindConnectorRecordByReferenceEntityAndCode */
    private $query;

    public function setup()
    {
        $this->query = new InMemoryFindConnectorRecordByReferenceEntityAndCode();
    }

    /**
     * @test
     */
    public function it_returns_null_when_finding_a_non_existent_record()
    {
        $result = ($this->query)(
            ReferenceEntityIdentifier::fromString('reference_entity'),
            RecordCode::fromString('non_existent_record_code')
        );

        Assert::assertNull($result);
    }

    /**
     * @test
     */
    public function it_returns_the_record_when_finding_an_existent_record()
    {
        $record = new ConnectorRecord(
            RecordCode::fromString('record_code'),
            LabelCollection::fromArray([]),
            Image::createEmpty(),
            []
        );
        $this->query->save(
            ReferenceEntityIdentifier::fromString('reference_entity'),
            RecordCode::fromString('record_code'),
            $record
        );

        $result = ($this->query)(
            ReferenceEntityIdentifier::fromString('reference_entity'),
            RecordCode::fromString('record_code')
        );

        Assert::assertNotNull($result);
        Assert::assertEquals(
            $record->normalize(),
            $result->normalize()
        );
    }
}
