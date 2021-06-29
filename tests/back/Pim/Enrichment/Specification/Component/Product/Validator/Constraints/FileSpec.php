<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\File;
use PhpSpec\ObjectBehavior;

class FileSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(File::class);
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

    function it_has_max_size_message()
    {
        $this->maxSizeMessage->shouldBe('The file %file_name% is too large (%file_size% %suffix%). The %attribute% attribute can not exceed %max_file_size% %suffix%.');
    }
}
