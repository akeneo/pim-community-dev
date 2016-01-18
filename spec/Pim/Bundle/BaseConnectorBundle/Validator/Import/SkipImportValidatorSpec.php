<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Validator\Import;

use PhpSpec\ObjectBehavior;

class SkipImportValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Validator\Import\SkipImportValidator');
    }

    function it_is_an_import_validator()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface');
    }

    function it_skips_an_import_validation($object)
    {
        $this->validate($object, [], [])->shouldReturn([]);
    }
}
