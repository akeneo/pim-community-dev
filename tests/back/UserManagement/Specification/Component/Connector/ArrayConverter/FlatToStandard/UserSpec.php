<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PhpSpec\ObjectBehavior;

class UserSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $checker)
    {
        $this->beConstructedWith($checker);
    }

    function it_converts(FieldsRequirementChecker $checker)
    {
        $fields = [
            'username' => 'julia',
            'email' => 'Julia@example.com',
            'password' => 'julia',
            'first_name' => 'Julia',
            'last_name' => 'Stark',
            'catalog_locale' => 'en_US',
            'user_locale' => 'en_US',
            'catalog_scope' => 'ecommerce',
            'default_tree' => 'men_2013',
            'roles' => 'ROLE_USER',
            'groups' => 'Redactor',
            'enabled' => '1',
            'timezone' => '',
        ];

        $checker->checkFieldsPresence($fields, ['username'])->shouldBeCalled();
        $checker->checkFieldsFilling($fields, ['username'])->shouldBeCalled();

        $this->convert($fields)->shouldReturn(
            [
                'username' => 'julia',
                'email' => 'Julia@example.com',
                'password' => 'julia',
                'first_name' => 'Julia',
                'last_name' => 'Stark',
                'catalog_locale' => 'en_US',
                'user_locale' => 'en_US',
                'catalog_scope' => 'ecommerce',
                'default_tree' => 'men_2013',
                'roles' => ['ROLE_USER'],
                'groups' => ['Redactor'],
                'enabled' => true,
                'timezone' => null,
            ]
        );
    }

    function it_converts_empty_strings_to_null(FieldsRequirementChecker $checker)
    {
        $fields = [
            'username' => 'julia',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'catalog_locale' => '',
            'user_locale' => '',
            'catalog_scope' => '',
            'default_tree' => '',
            'enabled' => '',
            'timezone' => '',
        ];

        $checker->checkFieldsPresence($fields, ['username'])->shouldBeCalled();
        $checker->checkFieldsFilling($fields, ['username'])->shouldBeCalled();

        $this->convert($fields)->shouldReturn(
            [
                'username' => 'julia',
                'email' => null,
                'first_name' => null,
                'last_name' => null,
                'catalog_locale' => null,
                'user_locale' => null,
                'catalog_scope' => null,
                'default_tree' => null,
                'enabled' => null,
                'timezone' => null,
            ]
        );
    }

    function it_converts_boolean_integers(FieldsRequirementChecker $checker)
    {
        $fields = [
            'username' => 'julia',
            'enabled' => 0,
        ];

        $checker->checkFieldsPresence($fields, ['username'])->shouldBeCalled();
        $checker->checkFieldsFilling($fields, ['username'])->shouldBeCalled();

        $this->convert($fields)->shouldReturn(
            [
                'username' => 'julia',
                'enabled' => false,
            ]
        );
    }

    function it_does_not_convert_wrong_boolean_value(FieldsRequirementChecker $checker)
    {
        $fields = [
            'username' => 'julia',
            'enabled' => 'some_wrong_data',
        ];

        $checker->checkFieldsPresence($fields, ['username'])->shouldBeCalled();
        $checker->checkFieldsFilling($fields, ['username'])->shouldBeCalled();

        $this->convert($fields)->shouldReturn(
            [
                'username' => 'julia',
                'enabled' => 'some_wrong_data',
            ]
        );
    }

    function it_converts_array_strings(FieldsRequirementChecker $checker)
    {
        $fields = [
            'username' => 'julia',
            'roles' => 'ROLE_ADMIN',
            'groups' => 'redactor,manager,user',
            'product_grid_filters' => 'family,family_variant',
        ];

        $checker->checkFieldsPresence($fields, ['username'])->shouldBeCalled();
        $checker->checkFieldsFilling($fields, ['username'])->shouldBeCalled();

        $this->convert($fields)->shouldReturn(
            [
                'username' => 'julia',
                'roles' => ['ROLE_ADMIN'],
                'groups' => ['redactor', 'manager', 'user'],
                'product_grid_filters' => ['family', 'family_variant'],
            ]
        );
    }

    function it_converts_an_avatar_filepath(FieldsRequirementChecker $checker)
    {
        $fields = [
            'username' => 'julia',
            'avatar' => 'files/image/avatar.png',
        ];

        $checker->checkFieldsPresence($fields, ['username'])->shouldBeCalled();
        $checker->checkFieldsFilling($fields, ['username'])->shouldBeCalled();

        $this->convert($fields)->shouldReturn(
            [
                'username' => 'julia',
                'avatar' => ['filePath' => 'files/image/avatar.png'],
            ]
        );
    }
}
