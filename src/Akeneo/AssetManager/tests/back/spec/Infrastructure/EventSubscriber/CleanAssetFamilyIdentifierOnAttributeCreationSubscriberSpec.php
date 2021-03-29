<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\EventSubscriber;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

final class CleanAssetFamilyIdentifierOnAttributeCreationSubscriberSpec extends ObjectBehavior
{
    public function let(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
        $this->beConstructedWith($assetFamilyRepository);
    }

    public function it_cleans_the_asset_family_identifier_on_creation_of_an_asset_collection_attribute(
        $assetFamilyRepository,
        AttributeInterface $attribute,
        AssetFamily $assetFamily,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($attribute);

        $attribute->getId()->willReturn(null);
        $attribute->getType()->willReturn(AssetCollectionType::ASSET_COLLECTION);
        $attribute->getProperty('reference_data_name')->willReturn('ZiggY');

        $assetFamilyRepository
            ->getByIdentifier(AssetFamilyIdentifier::fromString('ZiggY'))
            ->willReturn($assetFamily);

        $assetFamily->getIdentifier()->willReturn(AssetFamilyIdentifier::fromString('ziggy'));

        $attribute->setProperty('reference_data_name', 'ziggy')->shouldBeCalled();

        $this->cleanAssetFamilyIdentifier($event);
    }

    public function it_does_nothing_if_it_is_not_an_attribute(
        $assetFamilyRepository,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn(new \stdClass());
        $assetFamilyRepository->getByIdentifier(Argument::any())->shouldNotBeCalled();
        $this->cleanAssetFamilyIdentifier($event);
    }

    public function it_does_nothing_if_the_attribute_is_already_created(
        $assetFamilyRepository,
        GenericEvent $event,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($attribute);
        $attribute->getId()->willReturn(42);

        $assetFamilyRepository->getByIdentifier(Argument::any())->shouldNotBeCalled();
        $this->cleanAssetFamilyIdentifier($event);
    }

    public function it_does_nothing_if_the_attribute_is_not_of_type_asset_collection(
        $assetFamilyRepository,
        GenericEvent $event,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($attribute);
        $attribute->getId()->willReturn(42);
        $attribute->getType()->willReturn('pim_catalog_text');

        $assetFamilyRepository->getByIdentifier(Argument::any())->shouldNotBeCalled();
        $attribute->setProperty(Argument::cetera())->shouldNotBeCalled();

        $this->cleanAssetFamilyIdentifier($event);
    }

    public function it_does_nothing_if_the_asset_family_identifier_is_empty(
        $assetFamilyRepository,
        GenericEvent $event,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($attribute);
        $attribute->getId()->willReturn(42);
        $attribute->getType()->willReturn(AssetCollectionType::ASSET_COLLECTION);

        $attribute->getProperty('reference_data_name')->willReturn('');

        $assetFamilyRepository->getByIdentifier(Argument::any())->shouldNotBeCalled();
        $attribute->setProperty(Argument::cetera())->shouldNotBeCalled();
        $this->cleanAssetFamilyIdentifier($event);
    }

    public function it_does_nothing_if_the_the_asset_family_is_not_found(
        $assetFamilyRepository,
        GenericEvent $event,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($attribute);
        $attribute->getId()->willReturn(42);
        $attribute->getType()->willReturn(AssetCollectionType::ASSET_COLLECTION);

        $attribute->getProperty('reference_data_name')->willReturn('ZiggY');

        $assetFamilyRepository
            ->getByIdentifier(AssetFamilyIdentifier::fromString('ZiggY'))
            ->willReturn(null);

        $attribute->setProperty(Argument::cetera())->shouldNotBeCalled();
        $this->cleanAssetFamilyIdentifier($event);
    }
}
