<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\AssetFamily\Subscribers;

use Akeneo\AssetManager\Application\AssetFamily\Subscribers\SetNullOnDefaultAttributesDeletionSubscriber;
use Akeneo\AssetManager\Domain\Event\BeforeAttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

class SetNullOnDefaultAttributesDeletionSubscriberSpec extends ObjectBehavior
{
    function let(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
        $this->beConstructedWith($assetFamilyRepository);
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
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeIdentifier $attributeIdentifierLabel,
        AttributeIdentifier $attributeIdentifierImage,
        AttributeAsLabelReference $attributeAsLabelReference,
        AttributeAsImageReference $attributeAsImageReference,
        BeforeAttributeDeletedEvent $event,
        AssetFamily $assetFamily
    ) {
        $event->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $event->getAttributeIdentifier()->willReturn($attributeIdentifierLabel);

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)
            ->willReturn($assetFamily);

        $assetFamily->getAttributeAsLabelReference()->willReturn($attributeAsLabelReference);
        $assetFamily->getAttributeAsImageReference()->willReturn($attributeAsImageReference);

        $attributeAsLabelReference->isEmpty()->willReturn(false);
        $attributeAsImageReference->isEmpty()->willReturn(false);

        $attributeAsLabelReference->getIdentifier()->willReturn($attributeIdentifierLabel);
        $attributeAsImageReference->getIdentifier()->willReturn($attributeIdentifierImage);

        $attributeIdentifierLabel->equals($attributeIdentifierLabel)->willReturn(true);
        $attributeIdentifierLabel->equals($attributeIdentifierImage)->willReturn(false);

        $assetFamily->updateAttributeAsLabelReference(AttributeAsLabelReference::noReference())
            ->shouldBeCalled();

        $assetFamilyRepository->update($assetFamily)->shouldBeCalled();

        $this->beforeAttributeAsLabelOrImageIsDeleted($event);
    }
}
