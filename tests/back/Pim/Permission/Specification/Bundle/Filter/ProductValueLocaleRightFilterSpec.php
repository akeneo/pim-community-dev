<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductValueLocaleRightFilterSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        LocaleRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $cachedLocaleRepository,
        TokenInterface $token,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith(
            $tokenStorage,
            $authorizationChecker,
            $localeRepository,
            $cachedLocaleRepository,
            $attributeRepository
        );
    }

    function it_does_not_filter_a_product_value_if_the_user_is_granted_to_see_its_locale(
        $authorizationChecker,
        $cachedLocaleRepository,
        ValueInterface $price,
        AttributeInterface $priceAttribute,
        LocaleInterface $enUS,
        $attributeRepository
    ) {
        $price->getAttributeCode()->willReturn('price');
        $price->getLocaleCode()->willReturn('en_US');
        $price->isLocalizable()->willReturn(true);

        $attributeRepository->findOneByIdentifier('price')->willReturn($priceAttribute);
        $priceAttribute->isLocaleSpecific()->willReturn(false);

        $cachedLocaleRepository->findOneByIdentifier('en_US')->willReturn($enUS);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUS)->willReturn(true);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    function it_filters_a_product_value_if_the_user_is_not_granted_to_see_its_locale(
        $authorizationChecker,
        $cachedLocaleRepository,
        ValueInterface $price,
        AttributeInterface $priceAttribute,
        LocaleInterface $enUS,
        $attributeRepository
    ) {
        $price->getAttributeCode()->willReturn('price');
        $price->getLocaleCode()->willReturn('en_US');
        $attributeRepository->findOneByIdentifier('price')->willReturn($priceAttribute);

        $price->isLocalizable()->willReturn(true);
        $priceAttribute->isLocaleSpecific()->willReturn(false);
        $cachedLocaleRepository->findOneByIdentifier('en_US')->willReturn($enUS);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUS)->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(true);
    }

    function it_does_not_filter_a_product_value_if_the_attribute_is_not_localizable(
        ValueInterface $price,
        AttributeInterface $priceAttribute,
        $attributeRepository
    ) {
        $price->getAttributeCode()->willReturn('price');
        $price->isLocalizable()->willReturn(false);
        $attributeRepository->findOneByIdentifier('price')->willReturn($priceAttribute);

        $priceAttribute->isLocaleSpecific()->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    function it_filters_a_locale_specific_product_value_if_the_user_is_not_granted_on_all_locales(
        $authorizationChecker,
        $cachedLocaleRepository,
        ValueInterface $price,
        AttributeInterface $priceAttribute,
        LocaleInterface $enUS,
        LocaleInterface $frFR,
        $attributeRepository
    ) {
        $price->getAttributeCode()->willReturn('price');
        $price->isLocalizable()->willReturn(false);
        $attributeRepository->findOneByIdentifier('price')->willReturn($priceAttribute);

        $priceAttribute->getAvailableLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $priceAttribute->isLocaleSpecific()->willReturn(true);
        $cachedLocaleRepository->findOneByIdentifier('en_US')->willReturn($enUS);
        $cachedLocaleRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUS)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $frFR)->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(true);
    }

    function it_does_not_filter_a_locale_specific_product_value_if_the_user_is_granted_on_at_least_one_locale(
        $authorizationChecker,
        $cachedLocaleRepository,
        ValueInterface $price,
        AttributeInterface $priceAttribute,
        LocaleInterface $enUS,
        LocaleInterface $frFR,
        $attributeRepository
    ) {
        $price->getAttributeCode()->willReturn('price');
        $price->isLocalizable()->willReturn(false);
        $attributeRepository->findOneByIdentifier('price')->willReturn($priceAttribute);

        $priceAttribute->getAvailableLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $priceAttribute->isLocaleSpecific()->willReturn(true);
        $cachedLocaleRepository->findOneByIdentifier('en_US')->willReturn($enUS);
        $cachedLocaleRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUS)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $frFR)->willReturn(true);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow(\LogicException::class)->during(
            'filterObject',
            [$anOtherObject, 'pim:product_value:view', ['channels' => ['en_US']]]
        );
    }
}
