<?php

namespace spec\Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\File\FileExistsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditReferenceEntityHandlerSpec extends ObjectBehavior
{
    public function let(
        ReferenceEntityRepositoryInterface $repository,
        FileStorerInterface $storer,
        FileExistsInterface $fileExists
    ) {
        $this->beConstructedWith($repository, $storer, $fileExists);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditReferenceEntityHandler::class);
    }

    function it_edits_a_reference_entity_with_an_empty_image(
        ReferenceEntityRepositoryInterface $repository,
        ReferenceEntity $referenceEntity,
        EditReferenceEntityCommand $editReferenceEntityCommand,
        Image $image
    ) {
        $editReferenceEntityCommand->identifier = 'designer';
        $editReferenceEntityCommand->labels = ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'];
        $editReferenceEntityCommand->image = null;

        $repository->getByIdentifier(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn($referenceEntity);

        $referenceEntity->getImage()->willReturn($image);
        $image->isEmpty()->willReturn(true);

        $referenceEntity->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $referenceEntity->updateImage(Argument::that(function ($image) {
                return $image instanceof Image && $image->isEmpty();
            }))
            ->shouldBeCalled();

        $repository->update($referenceEntity)->shouldBeCalled();

        $this->__invoke($editReferenceEntityCommand);
    }

    function it_edits_a_reference_entity_with_an_uploaded_image(
        ReferenceEntityRepositoryInterface $repository,
        ReferenceEntity $referenceEntity,
        EditReferenceEntityCommand $editReferenceEntityCommand,
        FileStorerInterface $storer,
        FileInfoInterface $fileInfo,
        Image $image,
        FileExistsInterface $fileExists
    ) {
        $editReferenceEntityCommand->identifier = 'designer';
        $editReferenceEntityCommand->labels = ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'];
        $editReferenceEntityCommand->image = ['originalFilename' => 'image.jpg', 'filePath' => '/path/image.jpg'];

        $repository->getByIdentifier(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn($referenceEntity);

        $referenceEntity->getImage()->willReturn($image);
        $image->isEmpty()->willReturn(true);

        $referenceEntity->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $referenceEntity->updateImage(Argument::that(function ($image) {
                return $image instanceof Image && $image->getKey() === '/path/image.jpg';
            }))
            ->shouldBeCalled();

        $fileExists->__invoke('/path/image.jpg')->willReturn(false);

        $storer->store(Argument::type(\SplFileInfo::class), Argument::type('string'))
            ->willReturn($fileInfo);

        $fileInfo->getKey()
            ->willReturn('/path/image.jpg');

        $fileInfo->getOriginalFilename()
            ->willReturn('image.jpg');

        $repository->update($referenceEntity)->shouldBeCalled();

        $this->__invoke($editReferenceEntityCommand);
    }

    function it_edits_a_reference_entity_with_a_stored_image(
        ReferenceEntityRepositoryInterface $repository,
        ReferenceEntity $referenceEntity,
        EditReferenceEntityCommand $editReferenceEntityCommand,
        Image $image,
        FileExistsInterface $fileExists
    ) {
        $editReferenceEntityCommand->identifier = 'designer';
        $editReferenceEntityCommand->labels = ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'];
        $editReferenceEntityCommand->image = ['originalFilename' => 'image.jpg', 'filePath' => '/path/image.jpg'];

        $repository->getByIdentifier(Argument::type(ReferenceEntityIdentifier::class))
            ->willReturn($referenceEntity);

        $referenceEntity->getImage()->willReturn($image);
        $image->isEmpty()->willReturn(true);

        $referenceEntity->updateLabels(Argument::type(LabelCollection::class))
            ->shouldBeCalled();

        $referenceEntity->updateImage(Argument::that(function ($image) {
                return $image instanceof Image && $image->getKey() === '/path/image.jpg';
            }))
            ->shouldBeCalled();

        $fileExists->__invoke('/path/image.jpg')->willReturn(true);

        $repository->update($referenceEntity)->shouldBeCalled();

        $this->__invoke($editReferenceEntityCommand);
    }
}
