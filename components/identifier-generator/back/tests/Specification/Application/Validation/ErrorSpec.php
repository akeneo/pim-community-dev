<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation;

use PhpSpec\ObjectBehavior;

class ErrorSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'a message',
            ['parameter1' => 'value1'],
            'a path',
        );
    }

    public function it_should_be_normalized(): void
    {
        $this->normalize()->shouldReturn([
            'path' => 'a path',
            'message' => 'a message',
        ]);
    }
}
