<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;
use PhpSpec\ObjectBehavior;

class ViolationsExceptionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(new ErrorList([
            new Error('message1'),
            new Error('message2'),
        ]));
    }

    public function it_should_be_normalized()
    {
        $this->normalize()->shouldReturn([
            [
                'path' => null,
                'parameters' => [],
                'message' => 'message1'
            ],
            [
                'path' => null,
                'parameters' => [],
                'message' => 'message2'
            ],
        ]);
    }
}
