<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Email;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\Email as BaseEmail;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmailSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Email::class);
    }

    function it_is_a_base_email()
    {
        $this->shouldHaveType(BaseEmail::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('The %attribute% attribute requires an e-mail address.');
    }

    function it_has_attribute_code()
    {
        $this->attributeCode->shouldBe('');
    }
}
