<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat\Role;
use PhpSpec\ObjectBehavior;

class RoleSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $this->beConstructedWith($fieldsRequirementChecker);
    }

    function it_is_an_array_converter()
    {
        $this->shouldBeAnInstanceOf(Role::class);
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_a_role_in_flat_format(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $role = [
            'role' => 'ROLE_ADMINISTRATOR',
            'label' => 'Administrator',
            'permissions' => [
                ['id' => 'action:pim_enrich_product_create'],
                ['id' => 'action:pim_enrich_product_index'],
            ],
        ];

        $fieldsRequirementChecker->checkFieldsPresence($role, ['role', 'label'])->shouldBeCalled();

        $convertedRole = $this->convert($role);
        $convertedRole->shouldHaveKey('label');
        $convertedRole['role']->shouldBe('ROLE_ADMINISTRATOR');
        $convertedRole['label']->shouldBe('Administrator');
        $convertedRole->shouldHaveKey('permissions');
        $convertedRole['permissions']->shouldBe('action:pim_enrich_product_create,action:pim_enrich_product_index');
    }
}
