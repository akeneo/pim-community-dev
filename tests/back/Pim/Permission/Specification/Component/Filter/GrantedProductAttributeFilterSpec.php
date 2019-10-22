<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Permission\Component\Filter\GrantedProductAttributeFilter;
use Akeneo\Pim\Permission\Component\Attributes;
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
                '123' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Test with numeric attribute name',
                    ],
                ],
            ],
        ];

        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('123')->willReturn($attribute);
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
        AttributeGroupInterface $group
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
