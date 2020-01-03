<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\ApiBundle\Checker;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class DuplicateValueCheckerSpec extends ObjectBehavior
{
    function it_throws_exception_if_values_are_duplicated()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('check', [[
            'values' => [
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ['locale' => null, 'scope' => null, 'data' => 'optionA']
                ]
            ]
        ]]);
    }

    function it_does_not_throws_exception_if_values_are_different()
    {
        $this->shouldNotThrow(InvalidPropertyTypeException::class)->during('check', [[
            'values' => [
                'a_simple_select' => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ['locale' => null, 'scope' => 'ecommerce', 'data' => 'optionA']
                ]
            ]
        ]]);
    }
}
