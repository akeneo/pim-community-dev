<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\Subscribers;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\Subscribers\SetDefaultAttributesOnAssetFamilyCreationSubscriber;
use Akeneo\AssetManager\Domain\Event\AssetFamilyCreatedEvent;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SetDefaultAttributesOnAssetFamilyCreationSubscriberSpec extends ObjectBehavior
{
    function let(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        CreateAttributeHandler $createAttributeHandler
    ) {
        $this->beConstructedWith($assetFamilyRepository, $attributeRepository, $createAttributeHandler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetDefaultAttributesOnAssetFamilyCreationSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            AssetFamilyCreatedEvent::class => 'whenAssetFamilyCreated',
        ]);
    }

    function it_creates_attribute_as_label_and_as_image_after_asset_family_creation_and_sets_them_on_it(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        CreateAttributeHandler $createAttributeHandler,
        AssetFamilyCreatedEvent $assetFamilyCreatedEvent,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetFamily $assetFamily,
        TextAttribute $labelAttribute,
        ImageAttribute $imageAttribute
    ) {
        $assetFamilyCreatedEvent->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetFamilyIdentifier->normalize()->willReturn('designer');

        $createAttributeHandler->__invoke(Argument::type(CreateTextAttributeCommand::class))
            ->shouldBeCalled();
        $createAttributeHandler->__invoke(Argument::type(CreateImageAttributeCommand::class))
            ->shouldBeCalled();

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);

        $labelAttribute->getCode()->willReturn(AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_LABEL_CODE));
        $labelAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('label_brand_fingerprint'));
        $imageAttribute->getCode()->willReturn(AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_IMAGE_CODE));
        $imageAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('image_brand_fingerprint'));
        $attributeRepository->findByAssetFamily($assetFamilyIdentifier)->willReturn(
            [$labelAttribute, $imageAttribute]
        );

        $assetFamily->updateAttributeAsLabelReference(Argument::that(function ($attributeAsLabelReference) {
            return $attributeAsLabelReference instanceof AttributeAsLabelReference
                && 'label_brand_fingerprint' === $attributeAsLabelReference->normalize();

        }))->shouldBeCalled();
        $assetFamily->updateAttributeAsImageReference(Argument::that(function ($attributeAsImageReference) {
            return $attributeAsImageReference instanceof AttributeAsImageReference
                && 'image_brand_fingerprint' === $attributeAsImageReference->normalize();

        }))->shouldBeCalled();
        $assetFamilyRepository->update($assetFamily)->shouldBeCalled();

        $this->whenAssetFamilyCreated($assetFamilyCreatedEvent);
    }
}
