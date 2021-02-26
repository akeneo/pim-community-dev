<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard\Role;
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

    function it_converts_a_role_from_flat_to_standard_format(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $flat = [
            'role' => 'ROLE_ADMINISTRATOR',
            'label' => 'Administrators',
            'permissions' => 'action:pim_enrich_product_create,action:pim_enrich_product_index',
        ];

        $fieldsRequirementChecker->checkFieldsPresence($flat, ['role', 'label'])->shouldBeCalled();
        $fieldsRequirementChecker->checkFieldsFilling($flat, ['role', 'label'])->shouldBeCalled();

        $this->convert($flat)->shouldReturn([
            'role' => 'ROLE_ADMINISTRATOR',
            'label' => 'Administrators',
            'permissions' => [
                'action:pim_enrich_product_create',
                'action:pim_enrich_product_index',
            ],
        ]);
    }

    function it_can_convert_a_role_without_permission(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $flat = ['role' => 'ROLE_ADMINISTRATOR', 'label' => 'Administrators'];

        $fieldsRequirementChecker->checkFieldsPresence($flat, ['role', 'label'])->shouldBeCalled();
        $fieldsRequirementChecker->checkFieldsFilling($flat, ['role', 'label'])->shouldBeCalled();

        $this->convert($flat)->shouldReturn([
            'role' => 'ROLE_ADMINISTRATOR',
            'label' => 'Administrators',
        ]);
    }
}
