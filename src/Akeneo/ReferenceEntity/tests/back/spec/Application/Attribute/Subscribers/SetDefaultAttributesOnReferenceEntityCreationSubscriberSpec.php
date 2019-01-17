<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\Subscribers;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\Subscribers\SetDefaultAttributesOnReferenceEntityCreationSubscriber;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityCreatedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SetDefaultAttributesOnReferenceEntityCreationSubscriberSpec extends ObjectBehavior
{
    function let(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        CreateAttributeHandler $createAttributeHandler
    ) {
        $this->beConstructedWith($referenceEntityRepository, $attributeRepository, $createAttributeHandler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetDefaultAttributesOnReferenceEntityCreationSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            ReferenceEntityCreatedEvent::class => 'whenReferenceEntityCreated',
        ]);
    }

    function it_creates_attribute_as_label_and_as_image_after_reference_entity_creation_and_sets_them_on_it(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        CreateAttributeHandler $createAttributeHandler,
        ReferenceEntityCreatedEvent $referenceEntityCreatedEvent,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ReferenceEntity $referenceEntity,
        TextAttribute $labelAttribute,
        ImageAttribute $imageAttribute
    ) {
        $referenceEntityCreatedEvent->getReferenceEntityIdentifier()->willReturn($referenceEntityIdentifier);
        $referenceEntityIdentifier->normalize()->willReturn('designer');

        $createAttributeHandler->__invoke(Argument::type(CreateTextAttributeCommand::class))
            ->shouldBeCalled();
        $createAttributeHandler->__invoke(Argument::type(CreateImageAttributeCommand::class))
            ->shouldBeCalled();

        $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier)->willReturn($referenceEntity);

        $labelAttribute->getCode()->willReturn(AttributeCode::fromString(ReferenceEntity::DEFAULT_ATTRIBUTE_AS_LABEL_CODE));
        $labelAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('label_brand_fingerprint'));
        $imageAttribute->getCode()->willReturn(AttributeCode::fromString(ReferenceEntity::DEFAULT_ATTRIBUTE_AS_IMAGE_CODE));
        $imageAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('image_brand_fingerprint'));
        $attributeRepository->findByReferenceEntity($referenceEntityIdentifier)->willReturn(
            [$labelAttribute, $imageAttribute]
        );

        $referenceEntity->updateAttributeAsLabelReference(Argument::that(function ($attributeAsLabelReference) {
            return $attributeAsLabelReference instanceof AttributeAsLabelReference
                && 'label_brand_fingerprint' === $attributeAsLabelReference->normalize();

        }))->shouldBeCalled();
        $referenceEntity->updateAttributeAsImageReference(Argument::that(function ($attributeAsImageReference) {
            return $attributeAsImageReference instanceof AttributeAsImageReference
                && 'image_brand_fingerprint' === $attributeAsImageReference->normalize();

        }))->shouldBeCalled();
        $referenceEntityRepository->update($referenceEntity)->shouldBeCalled();

        $this->whenReferenceEntityCreated($referenceEntityCreatedEvent);
    }
}
