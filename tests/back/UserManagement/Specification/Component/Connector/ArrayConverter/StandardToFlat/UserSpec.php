<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class UserSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
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
            'enabled'        => '0',
            'timezone'       => '',
        ];

        $item = [
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
            'enabled'        => false,
            'timezone'       => null,
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
