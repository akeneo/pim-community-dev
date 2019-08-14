<?php

namespace Specification\Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Merger\NotGrantedValuesMerger;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedValuesMergerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        WriteValueCollectionFactory $valueCollectionFactory
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
        $this->shouldHaveType(NotGrantedValuesMerger::class);
    }

    function it_merges_values_in_product(
        $valueCollectionFactory,
        $attributeRepository,
        $localeRepository,
        $authorizationChecker,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        ValueInterface $textValue,
        ValueInterface $colorValue,
        AttributeRepositoryInterface $textAttribute,
        AttributeRepositoryInterface $colorAttribute,
        LocaleInterface $frLocale,
        LocaleInterface $enLocale,
        WriteValueCollection $values
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
        $fullProduct->getRawValues()->willReturn($allValues);

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

        $textValue->getAttributeCode()->willReturn('text');
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getLocaleCode()->willReturn(null);

        $colorValue->getAttributeCode()->willReturn('color');
        $colorValue->getScopeCode()->willReturn(null);
        $colorValue->getLocaleCode()->willReturn('en_US');

        $valueCollectionFactory->createFromStorageFormat($notGrantedValues)->willReturn(new WriteValueCollection([$textValue->getWrappedObject(), $colorValue->getWrappedObject()]));

        $filteredProduct->getFamilyVariant()->willReturn(null);
        $filteredProduct->getValues()->willReturn($values);
        $fullProduct->setValues($values)->shouldBeCalled();

        $fullProduct->addValue($textValue->getWrappedObject())->shouldBeCalled();
        $fullProduct->addValue($colorValue->getWrappedObject())->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_throws_an_exception_if_filtered_subject_is_not_an_entity_with_values()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithValuesInterface::class))
            ->during('merge', [new \stdClass(), new Product()]);
    }

    function it_throws_an_exception_if_full_subject_is_not_an_entity_with_values()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithValuesInterface::class))
            ->during('merge', [new Product(), new \stdClass()]);
    }
}
