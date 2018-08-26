<?php

namespace spec\Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity\EditEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity\EditEnrichedEntityHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditEnrichedEntityHandlerSpec extends ObjectBehavior
{
    public function let(EnrichedEntityRepositoryInterface $repository, FileStorerInterface $storer)
    {
        $this->beConstructedWith($repository, $storer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditEnrichedEntityHandler::class);
    }

    function it_edits_an_enriched_entity(
        EnrichedEntityRepositoryInterface $repository,
        EnrichedEntity $enrichedEntity,
        EditEnrichedEntityCommand $editEnrichedEntityCommand
    ) {
        $editEnrichedEntityCommand->identifier = 'designer';
        $editEnrichedEntityCommand->labels = ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'];
        $editEnrichedEntityCommand->image = ['originalFilename' => 'Akeneo.png', 'filePath' => '/tmp/Akeneo.png'];

        $repository->getByIdentifier(Argument::type(EnrichedEntityIdentifier::class))
            ->willReturn($enrichedEntity);

        $enrichedEntity->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $enrichedEntity->updateImage(Argument::type(Image::class))
            ->shouldBeCalled();

        $repository->update($enrichedEntity)->shouldBeCalled();

        $this->__invoke($editEnrichedEntityCommand);
    }
}
