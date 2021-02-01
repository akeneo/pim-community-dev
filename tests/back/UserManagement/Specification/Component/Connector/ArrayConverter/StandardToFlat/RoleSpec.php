<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat\Role;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\ActionAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use PhpSpec\ObjectBehavior;

class RoleSpec extends ObjectBehavior
{
    function let(
        FieldsRequirementChecker $fieldsRequirementChecker,
        AclManager $aclManager,
        AclExtensionInterface $extension1,
        AclExtensionInterface $extension2
    ) {
        $extension1->getExtensionKey()->willReturn('action');
        $extension1->getPermissions()->willReturn(['EXECUTE']);
        $extension2->getExtensionKey()->willReturn('entity');
        $extension2->getPermissions()->willReturn(['VIEW', 'CREATE']);
        $aclManager->getAllExtensions()->willReturn([$extension1, $extension2]);

        $this->beConstructedWith($fieldsRequirementChecker, $aclManager);
    }

    function it_is_an_array_converter()
    {
        $this->shouldBeAnInstanceOf(Role::class);
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_a_role_in_flat_format(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $role = [
            'label' => 'Administrator',
            'permissions' => [
                [
                    'id' => 'action:pim_enrich_product_create',
                    'name' => 'pim_enrich_product_create',
                    'group' => 'pim_enrich.acl_group.product',
                    'type' => 'action',
                    'permissions' => [
                        'EXECUTE' => ['name' => 'EXECUTE', 'access_level' => 1],
                    ],
                ],
                [
                    'id' => 'action:pim_enrich_product_index',
                    'name' => 'pim_enrich_product_index',
                    'group' => 'pim_enrich.acl_group.product',
                    'type' => 'action',
                    'permissions' => [
                        'EXECUTE' => ['name' => 'EXECUTE', 'access_level' => 1],
                    ],
                ],
                [
                    'id' => 'action:pim_enrich_product_remove',
                    'name' => 'pim_enrich_product_remove',
                    'group' => 'pim_enrich.acl_group.product',
                    'type' => 'action',
                    'permissions' => [
                        'EXECUTE' => ['name' => 'EXECUTE', 'access_level' => 0],
                    ],
                ],
            ],
        ];

        $fieldsRequirementChecker->checkFieldsPresence($role, ['label'])->shouldBeCalled();

        $convertedRole = $this->convert($role);
        $convertedRole->shouldHaveKey('label');
        $convertedRole['label']->shouldBe('Administrator');
        $convertedRole->shouldHaveKey('permissions');
        $convertedRole['permissions']->shouldBe('action:pim_enrich_product_create,action:pim_enrich_product_index');
    }

    function it_does_not_convert_privilege_with_several_permissions(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $role = [
            'label' => 'User',
            'permissions' => [
                [
                    'id' => 'entity:product1',
                    'name' => 'product1',
                    'group' => 'group',
                    'type' => 'entity',
                    'permissions' => [
                        'VIEW' => ['name' => 'VIEW', 'access_level' => 1],
                        'CREATE' => ['name' => 'CREATE', 'access_level' => 1],
                    ],
                ],
            ],
        ];

        $fieldsRequirementChecker->checkFieldsPresence($role, ['label'])->shouldBeCalled();

        $convertedRole = $this->convert($role);
        $convertedRole->shouldHaveKey('label');
        $convertedRole['label']->shouldBe('User');
        $convertedRole->shouldHaveKey('permissions');
        $convertedRole['permissions']->shouldBe('');
    }
}
