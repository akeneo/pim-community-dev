<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindRecordForConnectorByReferenceEntityAndCode;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindRecordForConnectorTest extends TestCase
{
    /** @var InMemoryFindRecordForConnectorByReferenceEntityAndCode */
    private $query;

    public function setup()
    {
        $this->query = new InMemoryFindRecordForConnectorByReferenceEntityAndCode();
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
        $record = new RecordForConnector(
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
        Assert::assertSame(
            $record->normalize(),
            $result->normalize()
        );
    }
}
