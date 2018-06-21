<?php

namespace spec\Akeneo\Asset\Component\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\UserManagement\Component\Model\Group;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssetCategoryNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $categoryNormalizer, CategoryAccessManager $accessManager)
    {
        $this->beConstructedWith($categoryNormalizer, $accessManager);
    }

    function it_normalize_an_category_with_access_informations(
        $accessManager,
        $categoryNormalizer,
        CategoryInterface $pants,
        Group $allGroup,
        Group $managerGroup,
        Group $adminGroup
    ) {
        $categoryNormalizer->normalize($pants, 'csv', ['versioning' => true])->willReturn(['foo' => 'bar']);

        $accessManager->getViewUserGroups($pants)->willReturn([$allGroup]);
        $allGroup->__toString()->willReturn('All');
        $accessManager->getEditUserGroups($pants)->willReturn([$managerGroup]);
        $managerGroup->__toString()->willReturn('Manager');
        $accessManager->getOwnUserGroups($pants)->shouldNotBeCalled();
        $adminGroup->__toString()->willReturn('Administrator');

        $this->normalize($pants, 'csv', ['versioning' => true])->shouldReturn([
            'foo'             => 'bar',
            'view_permission' => 'All',
            'edit_permission' => 'Manager',
        ]);
    }
}
