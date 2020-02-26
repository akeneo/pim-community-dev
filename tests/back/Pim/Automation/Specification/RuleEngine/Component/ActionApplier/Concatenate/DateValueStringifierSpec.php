<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\DateValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use PhpSpec\ObjectBehavior;

class DateValueStringifierSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_date']);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(DateValueStringifier::class);
    }

    function it_implements_value_stringifier_interface()
    {
        $this->shouldBeAnInstanceOf(ValueStringifierInterface::class);
    }

    function it_returns_supported_attribute_types()
    {
        $this->forAttributesTypes()->shouldBe(['pim_catalog_date']);
    }

    function it_stringifies_a_date_value()
    {
        $value = DateValue::value('code', new \DateTime('2000-10-30'));

        $this->stringify($value)->shouldBe('2000-10-30');
    }

    function it_stringifies_a_date_value_with_specific_format()
    {
        $value = DateValue::value('code', new \DateTime('2000-10-30'));

        $this->stringify($value, ['format' => 'd/m/Y'])->shouldBe('30/10/2000');
    }
}
