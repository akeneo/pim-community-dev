<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollection;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollectionFactory;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollection;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ChannelTranslationInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\EnrichBundle\Normalizer\IncompleteValuesNormalizer;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IncompleteValuesNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith(
            $normalizer,
            $requiredValueCollectionFactory,
            $incompleteValueCollectionFactory,
            $authorizationChecker
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IncompleteValuesNormalizer::class);
    }

    function it_supports_entity_with_family(EntityWithFamilyInterface $entityWithFamily, CategoryInterface $category)
    {
        $this->supportsNormalization($entityWithFamily, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($entityWithFamily, 'unsupported')->shouldReturn(false);
        $this->supportsNormalization($category, 'internal_api')->shouldReturn(false);
    }

    function it_returns_an_empty_array_if_the_entity_has_no_family(
        EntityWithFamilyInterface $entityWithFamily
    ) {
        $entityWithFamily->getFamily()->willReturn(null);
        $this->normalize($entityWithFamily)->shouldReturn([]);
    }

    function it_returns_no_attribute_if_the_user_has_no_edit_right_on_the_entity(
        $authorizationChecker,
        EntityWithFamilyInterface $entityWithFamily,
        FamilyInterface $family
    ) {
        $entityWithFamily->getFamily()->willReturn($family);
        $authorizationChecker->isGranted(Attributes::EDIT, $entityWithFamily)->willReturn(false);
        $this->normalize($entityWithFamily)->shouldReturn([]);
    }

    function it_skips_attribute_if_user_has_no_edit_right_on_its_attribute_group(
        $normalizer,
        $requiredValueCollectionFactory,
        $incompleteValueCollectionFactory,
        $authorizationChecker,
        EntityWithFamilyInterface $entityWithFamily,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirementEcommerce,
        ChannelInterface $ecommerce,
        RequiredValueCollection $requiredValueCollectionEcommerceFamily,
        RequiredValueCollection $requiredValueCollectionEcommerceFrench,
        IncompleteValueCollection $incompleteValueCollectionEcommerceFrench,
        Collection $incompleteValueCollectionEcommerceFrenchAttributesCollection,
        \Iterator $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator,
        ArrayCollection $ecommerceLocalesCollection,
        \Iterator $ecommerceLocalesCollectionIterator,
        LocaleInterface $frenchLocale,
        AttributeInterface $descriptionAttribute,
        AttributeInterface $pictureAttribute,
        AttributeGroupInterface $marketingGroup,
        AttributeGroupInterface $mediaGroup,
        AttributeTranslation $descriptionFrenchTranslation,
        AttributeTranslation $pictureFrenchTranslation,
        ChannelTranslationInterface $ecommerceFrenchTranslation
    ) {
        $entityWithFamily->getFamily()->willReturn($family);
        $authorizationChecker->isGranted(Attributes::EDIT, $entityWithFamily)->willReturn(true);

        $family->getAttributeRequirements()->willReturn([$attributeRequirementEcommerce]);
        $attributeRequirementEcommerce->getChannel()->willReturn($ecommerce);

        $requiredValueCollectionFactory->forChannel($family, $ecommerce)->willReturn($requiredValueCollectionEcommerceFamily);

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getTranslation('fr_FR')->willReturn($ecommerceFrenchTranslation);
        $ecommerceFrenchTranslation->getLabel()->willReturn('Ecommerce');
        $ecommerce->getLocales()->willReturn($ecommerceLocalesCollection);
        $ecommerceLocalesCollection->toArray()->willReturn([$frenchLocale]);
        $ecommerceLocalesCollection->getIterator()->willReturn($ecommerceLocalesCollectionIterator);
        $ecommerceLocalesCollectionIterator->rewind()->shouldBeCalled();
        $ecommerceLocalesCollectionIterator->next()->shouldBeCalled();
        $ecommerceLocalesCollectionIterator->valid()->willReturn(true, false);
        $ecommerceLocalesCollectionIterator->current()->willReturn($frenchLocale);

        $frenchLocale->getCode()->willReturn('fr_FR');
        $frenchLocale->getName()->willReturn('French');

        $requiredValueCollectionEcommerceFamily->filterByChannelAndLocale($ecommerce, $frenchLocale)
            ->willReturn($requiredValueCollectionEcommerceFrench);

        $incompleteValueCollectionFactory->forChannelAndLocale(
            $requiredValueCollectionEcommerceFrench,
            $ecommerce,
            $frenchLocale,
            $entityWithFamily
        )->willReturn($incompleteValueCollectionEcommerceFrench);

        $incompleteValueCollectionEcommerceFrench->attributes()->willReturn($incompleteValueCollectionEcommerceFrenchAttributesCollection);
        $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator->rewind()->shouldBeCalled();
        $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator->next()->shouldBeCalled();
        $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator->valid()->willReturn(true, true, false);
        $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator->current()->willReturn($descriptionAttribute, $pictureAttribute);
        $incompleteValueCollectionEcommerceFrenchAttributesCollection->getIterator()->willReturn($incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator);

        $descriptionAttribute->getCode()->willReturn('description');
        $descriptionAttribute->getGroup()->willReturn($marketingGroup);
        $descriptionAttribute->isLocalizable()->willReturn(false);
        $descriptionAttribute->getTranslation('fr_FR')->willReturn($descriptionFrenchTranslation);
        $descriptionFrenchTranslation->getLabel()->willReturn('Desription');

        $pictureAttribute->getCode()->willReturn('picture');
        $pictureAttribute->getGroup()->willReturn($mediaGroup);
        $pictureAttribute->isLocalizable()->willReturn(false);
        $pictureAttribute->getTranslation('fr_FR')->willReturn($pictureFrenchTranslation);
        $pictureFrenchTranslation->getLabel()->willReturn('Picture');

        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $marketingGroup)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $mediaGroup)->willReturn(true);

        $this->normalize($entityWithFamily)->shouldReturn([
            [
                'channel' => 'ecommerce',
                'labels' => ['fr_FR' => 'Ecommerce'],
                'locales' => [
                    'fr_FR' => [
                        'missing' => [
                            ['code' => 'picture', 'labels' => ['fr_FR' => 'Picture']]
                        ],
                        'label' => 'French'
                    ]
                ]
            ]
        ]);
    }

    function it_skips_localizable_attribute_on_a_specific_locale_if_user_has_no_edit_right_on_this_locale(
        $normalizer,
        $requiredValueCollectionFactory,
        $incompleteValueCollectionFactory,
        $authorizationChecker,
        EntityWithFamilyInterface $entityWithFamily,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirementEcommerce,
        ChannelInterface $ecommerce,
        RequiredValueCollection $requiredValueCollectionEcommerceFamily,
        RequiredValueCollection $requiredValueCollectionEcommerceFrench,
        RequiredValueCollection $requiredValueCollectionEcommerceGerman,
        IncompleteValueCollection $incompleteValueCollectionEcommerceFrench,
        IncompleteValueCollection $incompleteValueCollectionEcommerceGerman,
        Collection $incompleteValueCollectionEcommerceFrenchAttributesCollection,
        Collection $incompleteValueCollectionEcommerceGermanAttributesCollection,
        \Iterator $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator,
        \Iterator $incompleteValueCollectionEcommerceGermanAttributesCollectionIterator,
        ArrayCollection $ecommerceLocalesCollection,
        \Iterator $ecommerceLocalesCollectionIterator,
        LocaleInterface $frenchLocale,
        LocaleInterface $germanLocale,
        AttributeInterface $descriptionAttribute,
        AttributeInterface $pictureAttribute,
        AttributeGroupInterface $marketingGroup,
        AttributeGroupInterface $mediaGroup,
        AttributeTranslation $descriptionFrenchTranslation,
        AttributeTranslation $descriptionGermanTranslation,
        AttributeTranslation $pictureFrenchTranslation,
        AttributeTranslation $pictureGermanTranslation,
        ChannelTranslationInterface $ecommerceFrenchTranslation,
        ChannelTranslationInterface $ecommerceGermanTranslation
    ) {
        $entityWithFamily->getFamily()->willReturn($family);
        $authorizationChecker->isGranted(Attributes::EDIT, $entityWithFamily)->willReturn(true);

        $family->getAttributeRequirements()->willReturn([$attributeRequirementEcommerce]);
        $attributeRequirementEcommerce->getChannel()->willReturn($ecommerce);

        $requiredValueCollectionFactory->forChannel($family, $ecommerce)->willReturn($requiredValueCollectionEcommerceFamily);

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLocales()->willReturn($ecommerceLocalesCollection);
        $ecommerceLocalesCollection->toArray()->willReturn([$frenchLocale, $germanLocale]);
        $ecommerceLocalesCollection->getIterator()->willReturn($ecommerceLocalesCollectionIterator);
        $ecommerceLocalesCollectionIterator->rewind()->shouldBeCalled();
        $ecommerceLocalesCollectionIterator->next()->shouldBeCalled();
        $ecommerceLocalesCollectionIterator->valid()->willReturn(true, true, false);
        $ecommerceLocalesCollectionIterator->current()->willReturn($frenchLocale, $germanLocale);

        $descriptionAttribute->getCode()->willReturn('description');
        $descriptionAttribute->getGroup()->willReturn($marketingGroup);
        $descriptionAttribute->isLocalizable()->willReturn(true);

        $pictureAttribute->getCode()->willReturn('picture');
        $pictureAttribute->getGroup()->willReturn($mediaGroup);
        $pictureAttribute->isLocalizable()->willReturn(false);

        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $marketingGroup)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $mediaGroup)->willReturn(true);

        // FRENCH
        $frenchLocale->getCode()->willReturn('fr_FR');
        $frenchLocale->getName()->willReturn('French');

        $ecommerce->getTranslation('fr_FR')->willReturn($ecommerceFrenchTranslation);
        $ecommerceFrenchTranslation->getLabel()->willReturn('Ecommerce');

        $requiredValueCollectionEcommerceFamily->filterByChannelAndLocale($ecommerce, $frenchLocale)
            ->willReturn($requiredValueCollectionEcommerceFrench);

        $incompleteValueCollectionFactory->forChannelAndLocale(
            $requiredValueCollectionEcommerceFrench,
            $ecommerce,
            $frenchLocale,
            $entityWithFamily
        )->willReturn($incompleteValueCollectionEcommerceFrench);

        $incompleteValueCollectionEcommerceFrench->attributes()->willReturn($incompleteValueCollectionEcommerceFrenchAttributesCollection);
        $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator->rewind()->shouldBeCalled();
        $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator->next()->shouldBeCalled();
        $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator->valid()->willReturn(true, true, false);
        $incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator->current()->willReturn($descriptionAttribute, $pictureAttribute);
        $incompleteValueCollectionEcommerceFrenchAttributesCollection->getIterator()->willReturn($incompleteValueCollectionEcommerceFrenchAttributesCollectionIterator);

        $descriptionAttribute->getTranslation('fr_FR')->willReturn($descriptionFrenchTranslation);
        $descriptionFrenchTranslation->getLabel()->willReturn('Description');

        $pictureAttribute->getTranslation('fr_FR')->willReturn($pictureFrenchTranslation);
        $pictureFrenchTranslation->getLabel()->willReturn('Image');

        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $frenchLocale)->willReturn(true);

        // GERMAN
        $germanLocale->getCode()->willReturn('de_DE');
        $germanLocale->getName()->willReturn('German');

        $ecommerce->getTranslation('de_DE')->willReturn($ecommerceGermanTranslation);
        $ecommerceGermanTranslation->getLabel()->willReturn('Ecommerce');

        $requiredValueCollectionEcommerceFamily->filterByChannelAndLocale($ecommerce, $germanLocale)
            ->willReturn($requiredValueCollectionEcommerceGerman);

        $incompleteValueCollectionFactory->forChannelAndLocale(
            $requiredValueCollectionEcommerceGerman,
            $ecommerce,
            $germanLocale,
            $entityWithFamily
        )->willReturn($incompleteValueCollectionEcommerceGerman);

        $incompleteValueCollectionEcommerceGerman->attributes()->willReturn($incompleteValueCollectionEcommerceGermanAttributesCollection);
        $incompleteValueCollectionEcommerceGermanAttributesCollectionIterator->rewind()->shouldBeCalled();
        $incompleteValueCollectionEcommerceGermanAttributesCollectionIterator->next()->shouldBeCalled();
        $incompleteValueCollectionEcommerceGermanAttributesCollectionIterator->valid()->willReturn(true, true, false);
        $incompleteValueCollectionEcommerceGermanAttributesCollectionIterator->current()->willReturn($descriptionAttribute, $pictureAttribute);
        $incompleteValueCollectionEcommerceGermanAttributesCollection->getIterator()->willReturn($incompleteValueCollectionEcommerceGermanAttributesCollectionIterator);

        $descriptionAttribute->getTranslation('de_DE')->willReturn($descriptionGermanTranslation);
        $descriptionGermanTranslation->getLabel()->willReturn('Beschreibung');

        $pictureAttribute->getTranslation('de_DE')->willReturn($pictureGermanTranslation);
        $pictureGermanTranslation->getLabel()->willReturn('Bild');

        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $germanLocale)->willReturn(false);

        $this->normalize($entityWithFamily)->shouldReturn([
            [
                'channel' => 'ecommerce',
                'labels' => ['fr_FR' => 'Ecommerce', 'de_DE' => 'Ecommerce'],
                'locales' => [
                    'fr_FR' => [
                        'missing' => [
                            ['code' => 'description', 'labels' => ['fr_FR' => 'Description', 'de_DE' => 'Beschreibung']],
                            ['code' => 'picture', 'labels' => ['fr_FR' => 'Image', 'de_DE' => 'Bild']],
                        ],
                        'label' => 'French'
                    ],
                    'de_DE' => [
                        'missing' => [
                            ['code' => 'picture', 'labels' => ['fr_FR' => 'Image', 'de_DE' => 'Bild']],
                        ],
                        'label' => 'German'
                    ]
                ]
            ]
        ]);
    }
}
