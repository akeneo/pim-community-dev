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

namespace spec\Akeneo\EnrichedEntity\Application\Record\EditRecord;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditRecordHandlerSpec extends ObjectBehavior
{
    function let(RecordRepositoryInterface $recordRepository, FileStorerInterface $storer)
    {
        $this->beConstructedWith($recordRepository, $storer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditRecordHandler::class);
    }

    function it_edits_a_record(
        RecordRepositoryInterface $recordRepository,
        EditRecordCommand $editRecordCommand,
        Record $record
    ) {
        $editRecordCommand->identifier = 'brand_sony_a1677570-a278-444b-ab46-baa1db199392';
        $editRecordCommand->code = 'sony';
        $editRecordCommand->enrichedEntityIdentifier = 'brand';
        $editRecordCommand->labels = [
            'fr_FR' => 'Sony',
            'en_US' => 'Sony',
        ];

        $recordRepository->getByIdentifier(Argument::type(RecordIdentifier::class))->willReturn($record);

        $record->setLabels(Argument::type(LabelCollection::class))->shouldBeCalled();
        $record->updateImage(Argument::type(Image::class))->shouldBeCalled();
        $recordRepository->update($record)->shouldBeCalled();

        $this->__invoke($editRecordCommand);
    }

    function it_updates_a_record_with_same_image(
        RecordRepositoryInterface $recordRepository,
        FileStorerInterface $storer,
        EditRecordCommand $editRecordCommand,
        Record $record,
        Image $existingImage
    ) {
        $editRecordCommand->identifier = 'brand_sony_a1677570-a278-444b-ab46-baa1db199392';
        $editRecordCommand->code = 'sony';
        $editRecordCommand->enrichedEntityIdentifier = 'brand';
        $editRecordCommand->labels = [
            'fr_FR' => 'Sony',
            'en_US' => 'Sony',
        ];
        $editRecordCommand->image = [
            'filePath' => '/my/image/path',

        ];

        $existingImage->isEmpty()->willReturn(false);
        $existingImage->getKey()->willReturn('/my/image/path');
        $recordRepository->getByIdentifier(Argument::type(RecordIdentifier::class))->willReturn($record);
        $record->getImage()->willReturn($existingImage);

        $record->setLabels(Argument::type(LabelCollection::class))->shouldBeCalled();
        $storer->store(Argument::any(), 'catalogStorage')->shouldNotBeCalled();
        $recordRepository->update($record)->shouldBeCalled();

        $this->__invoke($editRecordCommand);
    }
}
