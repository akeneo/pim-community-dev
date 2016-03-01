<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;

class UserStandardConverterSpec extends ObjectBehavior
{
    function let(FieldsRequirementValidator $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_converts($validator)
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

        $validator->validateFields(
            $fields,
            ['username', 'email', 'password', 'enabled', 'roles', 'first_name', 'last_name'],
            true
        )->shouldBeCalled();

        $validator->validateFields(
            $fields,
            ['groups']
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
