<?php

namespace spec\Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateReferenceEntityHandlerSpec extends ObjectBehavior
{
    function let(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->beConstructedWith($referenceEntityRepository, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateReferenceEntityHandler::class);
    }

    function it_creates_a_new_record(ReferenceEntityRepositoryInterface $referenceEntityRepository) {
        $createReferenceEntityCommand = new CreateReferenceEntityCommand();
        $createReferenceEntityCommand->code = 'brand';
        $createReferenceEntityCommand->labels = [
            'en_US' => 'Intel',
            'fr_FR' => 'Intel',
        ];

        $referenceEntityRepository->create(Argument::that(function ($referenceEntity) {
            return $referenceEntity instanceof ReferenceEntity
                && 'brand' === $referenceEntity->getIdentifier()->normalize()
                && 'Intel' === $referenceEntity->getLabel('en_US')
                && 'Intel' === $referenceEntity->getLabel('fr_FR');
        }))->shouldBeCalled();

        $this->__invoke($createReferenceEntityCommand);
    }
}
