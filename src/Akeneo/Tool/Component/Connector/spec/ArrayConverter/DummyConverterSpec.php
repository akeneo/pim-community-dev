<?php

namespace spec\Akeneo\Tool\Component\Connector\ArrayConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class DummyConverterSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $checker)
    {
        $fieldsPresence = ['uuid', 'name', 'code'];
        $fieldsFilling = ['uuid', 'name'];

        $this->beConstructedWith($checker, $fieldsPresence, $fieldsFilling);
    }

    function it_checks_fields_when_converting($checker)
    {
        $item = [
            'uuid'     => 'effeacef4848484',
            'name'     => 'Long sword',
            'code'     => 'long_sword',
            'material' => ''
        ];

        $checker->checkFieldsPresence($item, ['uuid', 'name', 'code'])->shouldBeCalled();
        $checker->checkFieldsFilling($item, ['uuid', 'name'])->shouldBeCalled();

        $this->convert($item);
    }

    function it_converts_to_the_same_array_format()
    {
        $item = [
            'uuid'     => 'effeacef4848484',
            'name'     => 'Long sword',
            'code'     => 'long_sword',
            'material' => ''
        ];

        $this->convert($item)->shouldReturn($item);
    }
}
