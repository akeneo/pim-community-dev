<?php

namespace Specification\Akeneo\Asset\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Exception\ArrayConversionException;

class TagsSpec extends ObjectBehavior
{
    function it_converts()
    {
        $fields = [
            'tags' => 'dog,flowers'
        ];

        $this->convert($fields)->shouldReturn([
            ['code' => 'dog'],
            ['code' => 'flowers'],
        ]);
    }

    function it_throws_an_exception_if_required_fields_are_not_in_array()
    {
        $this->shouldThrow(new \LogicException('Field "tags" is expected, provided fields are "not_a_code"'))->during(
            'convert',
            [['not_a_code' => '']]
        );
    }
}
