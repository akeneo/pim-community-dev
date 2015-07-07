<?php

namespace spec\Pim\Bundle\ImportExportBundle\Validator\Constraints;

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
            ->shouldReturn('Pim\Bundle\ImportExportBundle\Validator\Constraints\WritableDirectoryValidator');
    }
}
