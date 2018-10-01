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

namespace spec\Akeneo\ReferenceEntity\Application\Record\CreateRecord;

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateRecordHandlerSpec extends ObjectBehavior
{
    function let(RecordRepositoryInterface $recordRepository)
    {
        $this->beConstructedWith($recordRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateRecordHandler::class);
    }

    function it_creates_and_save_a_new_record(
        RecordRepositoryInterface $recordRepository,
        CreateRecordCommand $createRecordCommand
    ) {
        $createRecordCommand->identifier = 'brand_intel_a1677570-a278-444b-ab46-baa1db199392';
        $createRecordCommand->code = 'intel';
        $createRecordCommand->referenceEntityIdentifier = 'brand';
        $createRecordCommand->labels = [
            'en_US' => 'Intel',
            'fr_FR' => 'Intel',
        ];

        $recordRepository->nextIdentifier(
            Argument::type(ReferenceEntityIdentifier::class),
            Argument::type(RecordCode::class)
        )->shouldBeCalled();
        $recordRepository->create(Argument::type(Record::class))->shouldBeCalled();

        $this->__invoke($createRecordCommand);
    }
}
