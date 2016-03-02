<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class FileSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\File');
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_extension_message()
    {
        $this->extensionsMessage->shouldBe('The file extension is not allowed (allowed extensions: %extensions%).');
    }

    function it_has_allowed_extensions()
    {
        $this->allowedExtensions->shouldBe(array());
    }
}
