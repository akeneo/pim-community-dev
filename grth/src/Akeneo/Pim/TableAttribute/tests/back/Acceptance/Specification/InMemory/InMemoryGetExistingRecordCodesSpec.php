<?php

declare(strict_types=1);

namespace Specification\Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;

class InMemoryGetExistingRecordCodesSpec extends ObjectBehavior
{
    function let($recordRepository, $record)
    {
        if (!\class_exists(AkeneoReferenceEntityBundle::class)) {
            throw new SkippingException('ReferenceEntity are not available in this scope');
        }

        $recordRepository->beADoubleOf(RecordRepositoryInterface::class);
        $record->beADoubleOf(Record::class);

        $this->beConstructedWith($recordRepository);

        $brandIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $recordRepository->getByReferenceEntityAndCode($brandIdentifier, RecordCode::fromString('A'))
            ->willReturn($record);
        $recordRepository->getByReferenceEntityAndCode($brandIdentifier, RecordCode::fromString('B'))
            ->willReturn($record);
        $recordRepository->getByReferenceEntityAndCode($brandIdentifier, RecordCode::fromString('C'))
            ->willThrow(new RecordNotFoundException());

        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordRepository->getByReferenceEntityAndCode($designerIdentifier, RecordCode::fromString('Bernard'))
            ->willThrow(new RecordNotFoundException());
        $recordRepository->getByReferenceEntityAndCode($designerIdentifier, RecordCode::fromString('Michel'))
            ->willReturn($record);
        $recordRepository->getByReferenceEntityAndCode($designerIdentifier, RecordCode::fromString('Patrick'))
            ->willThrow(new RecordNotFoundException());

        $colourIdentifier = ReferenceEntityIdentifier::fromString('colour');
        $recordRepository->getByReferenceEntityAndCode($colourIdentifier, RecordCode::fromString('purple'))
            ->willThrow(new RecordNotFoundException());
    }

    function it_returns_only_existing_record_codes()
    {
        $this->fromReferenceEntityIdentifierAndRecordCodes([
            'brand' => ['A', 'B', 'C'],
            'designer' => ['Bernard', 'Michel', 'Patrick'],
            'colour' => ['purple'],
        ])->shouldReturn([
            'brand' => ['A', 'B'],
            'designer' => [1 => 'Michel'],
        ]);
    }
}
