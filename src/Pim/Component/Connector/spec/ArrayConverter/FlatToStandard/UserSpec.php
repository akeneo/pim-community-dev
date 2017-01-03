<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

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
        ]);
    }
}
