<?php

declare(strict_types=1);


/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Application\Record\SearchRecord;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use PhpSpec\ObjectBehavior;

class SearchConnectorRecordResultSpec extends ObjectBehavior
{
    function it_can_be_constructed_only_with_connector_records()
    {
        $this->beConstructedThrough('createFromSearchAfterQuery', [
            [
                new ConnectorRecord(
                    RecordCode::fromString('record_code_1'),
                    [],
                    new \DateTimeImmutable(),
                    new \DateTimeImmutable()
                ),
                new \StdClass(),
                new ConnectorRecord(
                    RecordCode::fromString('record_code_2'),
                    [],
                    new \DateTimeImmutable(),
                    new \DateTimeImmutable()
                ),
            ],
            'last_sort_value'
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_returns_the_records()
    {
        $record1 = new ConnectorRecord(
            RecordCode::fromString('record_code_1'),
            [],
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
        $record2 = new ConnectorRecord(
            RecordCode::fromString('record_code_2'),
            [],
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
        $this->beConstructedThrough('createFromSearchAfterQuery', [
            [$record1, $record2],
            'last_sort_value'
        ]);

        $this->records()->shouldBeArray();
        $this->records()->shouldHaveCount(2);
        $this->records()[0]->shouldBe($record1);
        $this->records()[1]->shouldBe($record2);
    }

    function it_returns_the_last_sort_value()
    {
        $this->beConstructedThrough('createFromSearchAfterQuery', [
            [],
            'last_sort_value'
        ]);

        $this->lastSortValue()->shouldBe('last_sort_value');
    }
}
