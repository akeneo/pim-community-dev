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

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\DefaultValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use PhpSpec\ObjectBehavior;

class DefaultValueStringifierSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['type1', 'type2']);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(DefaultValueStringifier::class);
    }

    function it_implements_value_stringifier_interface()
    {
        $this->shouldBeAnInstanceOf(ValueStringifierInterface::class);
    }

    function it_returns_supported_attribute_types()
    {
        $this->forAttributesTypes()->shouldBe(['type1', 'type2']);
    }

    function it_stringifies_a_value(ValueInterface $value)
    {
        $value->__toString()->willReturn('the data');

        $this->stringify($value)->shouldBe('the data');
    }
}
