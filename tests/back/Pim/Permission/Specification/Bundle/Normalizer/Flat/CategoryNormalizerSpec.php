<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Normalizer\Flat;

use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $categoryNormalizer, CategoryAccessManager $accessManager)
    {
        $this->beConstructedWith($categoryNormalizer, $accessManager);
    }

    function it_normalize_a_category_with_access_informations($accessManager, $categoryNormalizer, CategoryInterface $pants, Group $allGroup, Group $managerGroup, Group $adminGroup)
    {
        $categoryNormalizer->normalize($pants, 'csv', ['versioning' => true])->willReturn(['foo' => 'bar']);

        $accessManager->getViewUserGroups($pants)->willReturn([$allGroup]);
        $allGroup->__toString()->willReturn('All');
        $accessManager->getEditUserGroups($pants)->willReturn([$managerGroup]);
        $managerGroup->__toString()->willReturn('Manager');
        $accessManager->getOwnUserGroups($pants)->willReturn([$adminGroup]);
        $adminGroup->__toString()->willReturn('Administrator');

        $this->normalize($pants, 'csv', ['versioning' => true])->shouldReturn([
            'foo'             => 'bar',
            'view_permission' => 'All',
            'edit_permission' => 'Manager',
            'own_permission'  => 'Administrator'
        ]);
    }
}
