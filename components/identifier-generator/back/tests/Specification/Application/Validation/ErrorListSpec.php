<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use PhpSpec\ObjectBehavior;

class ErrorListSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            new Error('message1'),
            new Error('message2'),
        ]);
    }

    public function it_should_be_normalized()
    {
        $this->normalize()->shouldReturn([
            [
                'path' => null,
                'message' => 'message1',
            ],
            [
                'path' => null,
                'message' => 'message2',
            ],
        ]);
    }
}
