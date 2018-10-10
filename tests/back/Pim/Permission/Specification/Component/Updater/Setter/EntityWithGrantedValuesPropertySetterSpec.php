<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater\Setter;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\Permission\Component\Updater\Setter\EntityWithGrantedValuesPropertySetter;
use Prophecy\Argument;
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
        $attributeName->getGroup()->willReturn($marketingGroup);
        $marketingGroup->getCode()->willReturn('marketing');
        $propertySetter->setData($entityWithValues, 'a_name', $data, $options);

        $authorizationChecker->isGranted([Attributes::EDIT_ATTRIBUTES])->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ATTRIBUTES])->willReturn(true);

        $this->shouldNotThrow(
            new ResourceAccessDeniedException(
                'Attribute "a_name" belongs to the attribute group "marketing" on which you only have view permission.'
            )
        )->during('setData', [$entityWithValues, 'a_name', $data, $options]);
    }
}
