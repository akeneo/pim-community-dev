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

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierRegistry;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ValueStringifierRegistrySpec extends ObjectBehavior
{
    function let(ValueStringifierInterface $valueStringifier1, ValueStringifierInterface $valueStringifier2)
    {
        $valueStringifier1->forAttributesTypes()->willReturn(['type1', 'type2']);
        $valueStringifier2->forAttributesTypes()->willReturn(['type3', 'type4']);

        $this->beConstructedWith([$valueStringifier1, $valueStringifier2]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ValueStringifierRegistry::class);
    }

    function it_returns_an_stringifier_from_attribute_type(
        ValueStringifierInterface $valueStringifier1,
        ValueStringifierInterface $valueStringifier2
    ) {
        $this->getStringifier('type1')->shouldBe($valueStringifier1);
        $this->getStringifier('type3')->shouldBe($valueStringifier2);
    }

    function it_returns_null_for_unknown_type()
    {
        $this->getStringifier('type8')->shouldBe(null);
    }
}
