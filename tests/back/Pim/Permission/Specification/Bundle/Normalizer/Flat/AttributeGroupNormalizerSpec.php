<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Normalizer\Flat;

use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $attributeGroupNormalizer, AttributeGroupAccessManager $accessManager)
    {
        $this->beConstructedWith($attributeGroupNormalizer, $accessManager);
    }

    function it_normalize_a_attribute_group_with_access_informations($accessManager, $attributeGroupNormalizer, AttributeGroupInterface $marketing, Group $allGroup, Group $managerGroup, Group $adminGroup)
    {
        $attributeGroupNormalizer->normalize($marketing, 'csv', ['versioning' => true])->willReturn(['foo' => 'bar']);

        $accessManager->getViewUserGroups($marketing)->willReturn([$allGroup]);
        $allGroup->__toString()->willReturn('All');
        $accessManager->getEditUserGroups($marketing)->willReturn([$managerGroup]);
        $managerGroup->__toString()->willReturn('Manager');

        $this->normalize($marketing, 'csv', ['versioning' => true])->shouldReturn([
            'foo'             => 'bar',
            'view_permission' => 'All',
            'edit_permission' => 'Manager'
        ]);
    }
}
