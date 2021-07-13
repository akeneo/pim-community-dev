<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\File;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class FileSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(File::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_extension_message()
    {
        $this->extensionsMessage->shouldBe('The %type% file extension is not allowed for the %attribute% attribute. Allowed extensions are %extensions%.');
    }

    function it_has_allowed_extensions()
    {
        $this->allowedExtensions->shouldBe(array());
    }

    function it_has_attribute_code()
    {
        $this->attributeCode->shouldBe('');
    }

    function it_has_max_size_message()
    {
        $this->maxSizeMessage->shouldBe('The file %file_name% is too large (%file_size% %suffix%). The %attribute% attribute can not exceed %max_file_size% %suffix%.');
    }
}
