<?php

namespace Specification\Akeneo\Pim\Permission\Component\Normalizer\Standard;

use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Component\Normalizer\Standard\AttributeGroupNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        AttributeGroupAccessManager $accessManager,
    ) {
        $this->beConstructedWith($normalizer, $accessManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_same_normalization_as_decorated_normalizer(
        NormalizerInterface $normalizer
    ) {
        $normalizer->supportsNormalization('supported', null)->willReturn(true);
        $this->supportsNormalization('supported')->shouldReturn(true);

        $normalizer->supportsNormalization('not_supported', null)->willReturn(false);
        $this->supportsNormalization('not_supported')->shouldReturn(false);
    }

    function it_normalizes_an_attribute_group_permissions(
        AttributeGroupInterface $attributeGroup,
        NormalizerInterface $normalizer,
        AttributeGroupAccessManager $accessManager,
    ) {
        $normalizer->normalize($attributeGroup, null, [])->willReturn([]);

        $viewGroup1 = new Group('View Group 1');
        $viewGroup1->setType(Group::TYPE_DEFAULT);
        $viewGroup2 = new Group('View Group 2');
        $viewGroup2->setType('app');
        $viewGroup3 = new Group('View Group 3');
        $viewGroup3->setType(Group::TYPE_DEFAULT);

        $accessManager->getViewUserGroups($attributeGroup)->willReturn([
            $viewGroup1,
            $viewGroup2,
            $viewGroup3,
        ]);

        $editGroup1 = new Group('Edit Group 1');
        $editGroup1->setType('app');
        $editGroup2 = new Group('Edit Group 2');
        $editGroup2->setType(Group::TYPE_DEFAULT);

        $accessManager->getEditUserGroups($attributeGroup)->willReturn([
            $editGroup1,
            $editGroup2,
        ]);

        $this->normalize($attributeGroup)
            ->shouldReturn([
                'permissions' => [
                    'view' => [
                        0 => 'View Group 1',
                        2 => 'View Group 3',
                    ],
                    'edit' => [
                        1 => 'Edit Group 2',
                    ],
                ]
            ]);
    }
}
