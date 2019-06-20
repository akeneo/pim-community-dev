<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\ReferenceEntity\Subscribers;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\Subscribers\SetNullOnDefaultAttributesDeletionSubscriber;
use Akeneo\ReferenceEntity\Domain\Event\BeforeAttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use PhpSpec\ObjectBehavior;

class SetNullOnDefaultAttributesDeletionSubscriberSpec extends ObjectBehavior
{
    function let(ReferenceEntityRepositoryInterface $referenceEntityRepository)
    {
        $this->beConstructedWith($referenceEntityRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetNullOnDefaultAttributesDeletionSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            BeforeAttributeDeletedEvent::class => 'beforeAttributeAsLabelOrImageIsDeleted',
        ]);
    }

    function it_unsets_attribute_as_label_or_image_when_the_attribute_is_deleted(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeIdentifier $attributeIdentifierLabel,
        AttributeIdentifier $attributeIdentifierImage,
        AttributeAsLabelReference $attributeAsLabelReference,
        AttributeAsImageReference $attributeAsImageReference,
        BeforeAttributeDeletedEvent $event,
        ReferenceEntity $referenceEntity
    ) {
        $event->getReferenceEntityIdentifier()->willReturn($referenceEntityIdentifier);
        $event->getAttributeIdentifier()->willReturn($attributeIdentifierLabel);

        $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier)
            ->willReturn($referenceEntity);

        $referenceEntity->getAttributeAsLabelReference()->willReturn($attributeAsLabelReference);
        $referenceEntity->getAttributeAsImageReference()->willReturn($attributeAsImageReference);

        $attributeAsLabelReference->isEmpty()->willReturn(false);
        $attributeAsImageReference->isEmpty()->willReturn(false);

        $attributeAsLabelReference->getIdentifier()->willReturn($attributeIdentifierLabel);
        $attributeAsImageReference->getIdentifier()->willReturn($attributeIdentifierImage);

        $attributeIdentifierLabel->equals($attributeIdentifierLabel)->willReturn(true);
        $attributeIdentifierLabel->equals($attributeIdentifierImage)->willReturn(false);

        $referenceEntity->updateAttributeAsLabelReference(AttributeAsLabelReference::noReference())
            ->shouldBeCalled();

        $referenceEntityRepository->update($referenceEntity)->shouldBeCalled();

        $this->beforeAttributeAsLabelOrImageIsDeleted($event);
    }
}
