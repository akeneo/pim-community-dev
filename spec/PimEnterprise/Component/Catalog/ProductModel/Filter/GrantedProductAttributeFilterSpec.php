<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\Catalog\ProductModel\Filter;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface;
use PimEnterprise\Component\Catalog\ProductModel\Filter\GrantedProductAttributeFilter;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GrantedProductAttributeFilterSpec extends ObjectBehavior
{
    function let(
        AttributeFilterInterface $productAttributeFilter,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($productAttributeFilter, $attributeRepository, $localeRepository, $authorizationChecker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GrantedProductAttributeFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_filters_when_attributes_and_locales_are_granted(
        $productAttributeFilter,
        $attributeRepository,
        $localeRepository,
        $authorizationChecker,
        AttributeInterface $attribute,
        AttributeGroupInterface $group,
        LocaleInterface $locale
    ) {
        $data = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'My very awesome T-shirt',
                    ],
                ],
            ],
        ];

        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->getGroup()->willReturn($group);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group)->willReturn(true);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)->willReturn(true);

        $productAttributeFilter->filter($data)->willReturn($data);

        $this->filter($data)->shouldReturn($data);
    }

    function it_throws_exception_when_filters_locale_not_granted(
        $attributeRepository,
        $localeRepository,
        $authorizationChecker,
        AttributeInterface $attribute,
        AttributeGroupInterface $group,
        LocaleInterface $locale
    ) {
        $data = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'My very awesome T-shirt',
                    ],
                ],
            ],
        ];

        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->getGroup()->willReturn($group);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group)->willReturn(true);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)->willReturn(false);

        $this->shouldThrow(
            UnknownPropertyException::class
        )->during(
            'filter',
            [$data]
        );
    }

    function it_throws_exception_when_filters_attribute_not_granted(
        $attributeRepository,
        $authorizationChecker,
        AttributeInterface $attribute,
        AttributeGroupInterface $group,
        LocaleInterface $locale
    ) {
        $data = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values' => [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'My very awesome T-shirt',
                    ],
                ],
            ],
        ];

        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->getGroup()->willReturn($group);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group)->willReturn(false);

        $this->shouldThrow(
            UnknownPropertyException::class
        )->during(
            'filter',
            [$data]
        );
    }
}
