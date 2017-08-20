<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedValuesMergerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        ValueCollectionFactoryInterface $valueCollectionFactory
    )
    {
        $this->beConstructedWith($authorizationChecker, $attributeRepository, $localeRepository, $valueCollectionFactory);
    }

    function it_implements_a_not_granted_data_merger_interface()
    {
        $this->shouldImplement(NotGrantedDataMergerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Merger\NotGrantedValuesMerger');
    }

    function it_does_not_merge_values_if_there_were_no_value_in_product(ProductInterface $product)
    {
        $product->getRawValues()->willReturn([]);
        $this->merge($product, [])->shouldReturn(null);
    }

    function it_merges_values_in_product(
        $valueCollectionFactory,
        $attributeRepository,
        $localeRepository,
        $authorizationChecker,
        ProductInterface $product,
        ValueInterface $textValue,
        ValueInterface $colorValue,
        AttributeRepositoryInterface $textAttribute,
        AttributeRepositoryInterface $colorAttribute,
        LocaleInterface $frLocale,
        LocaleInterface $enLocale
    ) {
        $allValues = [
            'text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a text'
                ],
            ],
            'color' => [
                '<all_channels>' => [
                    'en_US' => false,
                    'fr_FR' => false,
                ],
            ],
        ];
        $product->getRawValues()->willReturn($allValues);

        $notGrantedValues = [
            'text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a text'
                ],
            ],
            'color' => [
                '<all_channels>' => [
                    'en_US' => false,
                ],
            ],
        ];

        $attributeRepository->findOneByIdentifier('text')->willReturn($textAttribute);
        $attributeRepository->findOneByIdentifier('color')->willReturn($colorAttribute);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $textAttribute)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $colorAttribute)->willReturn(true);

        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frLocale);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $frLocale)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enLocale)->willReturn(false);

        $valueCollectionFactory->createFromStorageFormat($notGrantedValues)->willReturn([$textValue, $colorValue]);
        $product->addValue($textValue)->shouldBeCalled();
        $product->addValue($colorValue)->shouldBeCalled();

        $this->merge($product, [])->shouldReturn(null);
    }

    function it_throws_an_exception_if_subject_is_not_an_entity_with_values()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithValuesInterface::class))
            ->during('merge', [new \stdClass()]);
    }
}
