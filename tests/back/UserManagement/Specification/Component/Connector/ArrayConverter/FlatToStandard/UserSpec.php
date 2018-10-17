<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class UserSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $checker)
    {
        $this->beConstructedWith($checker);
    }

    function it_converts($checker)
    {
        $fields = [
            'username'       => 'julia',
            'email'          => 'Julia@example.com',
            'password'       => 'julia',
            'first_name'     => 'Julia',
            'last_name'      => 'Stark',
            'catalog_locale' => 'en_US',
            'user_locale'    => 'en_US',
            'catalog_scope'  => 'ecommerce',
            'default_tree'   => 'men_2013',
            'roles'          => 'ROLE_USER',
            'groups'         => 'Redactor',
            'enabled'        => '1',
            'timezone'       => '',
        ];

        $checker->checkFieldsPresence(
            $fields,
            ['username', 'email', 'password', 'enabled', 'roles', 'first_name', 'last_name', 'groups']
        )->shouldBeCalled();

        $checker->checkFieldsFilling(
            $fields,
            ['username', 'email', 'password', 'enabled', 'roles', 'first_name', 'last_name']
        )->shouldBeCalled();

        $this->convert($fields)->shouldReturn([
            'username'       => 'julia',
            'email'          => 'Julia@example.com',
            'password'       => 'julia',
            'first_name'     => 'Julia',
            'last_name'      => 'Stark',
            'catalog_locale' => 'en_US',
            'user_locale'    => 'en_US',
            'catalog_scope'  => 'ecommerce',
            'default_tree'   => 'men_2013',
            'roles'          => ['ROLE_USER'],
            'groups'         => ['Redactor'],
            'enabled'        => true,
            'timezone'       => null,
        ]);
    }

    function it_converts_boolean_strings($checker)
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
            'enabled' => 1,
            'email_notifications' => 1,
            'timezone' => '',
        ];

        $checker->checkFieldsPresence(
            $fields,
            ['username', 'email', 'password', 'enabled', 'roles', 'first_name', 'last_name', 'groups']
        )->shouldBeCalled();

        $checker->checkFieldsFilling(
            $fields,
            ['username', 'email', 'password', 'enabled', 'roles', 'first_name', 'last_name']
        )->shouldBeCalled();

        $this->convert($fields)->shouldReturn([
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
            'email_notifications' => true,
            'timezone' => null,
        ]);
    }
}
