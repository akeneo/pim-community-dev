<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\WritableDirectoryValidator;
use PhpSpec\ObjectBehavior;

class WritableDirectorySpec extends ObjectBehavior
{
    function it_is_a_constraint()
    {
        $this->shouldHaveType('Symfony\Component\Validator\Constraint');
    }

    function it_has_a_message()
    {
        $this->message->shouldBe('This directory is not writable');
    }

    function it_has_a_message_for_invalid_directory()
    {
        $this->invalidMessage->shouldBe('This directory is not valid');
    }

    function it_is_validated_by_writable_validator()
    {
        $this
            ->validatedBy()
            ->shouldReturn(WritableDirectoryValidator::class);
    }
}
