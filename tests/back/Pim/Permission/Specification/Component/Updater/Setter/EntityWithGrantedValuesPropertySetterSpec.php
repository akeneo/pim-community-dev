<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater\Setter;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\Permission\Component\Updater\Setter\EntityWithGrantedValuesPropertySetter;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EntityWithGrantedValuesPropertySetterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($propertySetter, $authorizationChecker, $attributeRepository, $localeRepository);
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(PropertySetterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EntityWithGrantedValuesPropertySetter::class);
    }

    function it_sets_values(
        $propertySetter,
        $authorizationChecker,
        $attributeRepository,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attributeName,
        AttributeGroupInterface $marketingGroup
    ) {
        $options = ['locale' => 'fr_FR', 'scope' => 'ecommerce'];
        $data = ['values' => ['a_name' => [['data' => 'name', 'locale' => null, 'scope' => null]]]];
        $attributeRepository->findOneByIdentifier('a_name')->willReturn($attributeName);
        $attributeName->getCode()->willReturn('a_name');
        $marketingGroup->getCode()->willReturn('marketing');
        $propertySetter->setData($entityWithValues, 'a_name', $data, $options);

        $authorizationChecker->isGranted([Attributes::EDIT_ATTRIBUTES], $attributeName)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ATTRIBUTES], $attributeName)->willReturn(true);

        $this->shouldNotThrow(
            new ResourceAccessDeniedException(
                'Attribute "a_name" belongs to the attribute group "marketing" on which you only have view permission.'
            )
        )->during('setData', [$entityWithValues, 'a_name', $data, $options]);
    }

    function it_throws_an_exception_if_the_view_and_edit_attribute_group_permissions_are_not_granted(
        $attributeRepository,
        $localeRepository,
        $authorizationChecker,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $attribute->getCode()->willReturn('a_name');

        $attributeRepository->findOneByIdentifier('a_name')->willReturn($attribute);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($locale);

        $authorizationChecker->isGranted([Attributes::VIEW_ATTRIBUTES], $attribute)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::EDIT_ATTRIBUTES], $attribute)->willReturn(false);

        $options = ['locale' => null, 'scope' => null];
        $data = [
            'values' => [
                'a_name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'name',
                    ]
                ]
            ]
        ];

        $this
            ->shouldThrow(UnknownAttributeException::class)
            ->during('setData', [$entityWithValues, 'a_name', $data, $options]);
    }

    function it_throws_an_exception_if_the_view_and_edit_attribute_locale_permissions_are_not_granted(
        $attributeRepository,
        $localeRepository,
        $authorizationChecker,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $attribute->getCode()->willReturn('a_name');

        $attributeRepository->findOneByIdentifier('a_name')->willReturn($attribute);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($locale);

        $authorizationChecker->isGranted([Attributes::VIEW_ATTRIBUTES], $attribute)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::EDIT_ATTRIBUTES], $attribute)->willReturn(true);

        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $locale)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::EDIT_ITEMS], $locale)->willReturn(false);

        $options = ['locale' => 'fr_FR', 'scope' => null];
        $data = [
            'values' => [
                'a_name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'name',
                    ]
                ]
            ]
        ];

        $this
            ->shouldThrow(UnknownAttributeException::class)
            ->during('setData', [$entityWithValues, 'a_name', $data, $options]);
    }
}
