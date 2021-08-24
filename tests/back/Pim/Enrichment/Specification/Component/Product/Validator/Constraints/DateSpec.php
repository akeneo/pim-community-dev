<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Date;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Date::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('The %attribute% attribute requires a valid date. Please use the following format %date_format% for dates.');
    }

    function it_has_attribute_code()
    {
        $this->attributeCode->shouldBe('');
    }
}
