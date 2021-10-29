<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\Comparator;

use Akeneo\Pim\TableAttribute\Infrastructure\Value\Comparator\TableValueComparator;
use PhpSpec\ObjectBehavior;

class TableValueComparatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TableValueComparator::class);
    }

    function it_supports_table_type_only()
    {
        $this->supports('pim_catalog_table')->shouldBe(true);
        $this->supports('pim_catalog_text')->shouldBe(false);
    }

    function it_returns_null_when_value_are_equals()
    {
        $original = ['data' => [['foo' => 'bar']]];
        $this->compare(['locale' => null, 'scope' => 'mobile', 'data' => [['foo' => 'bar']]], $original)
            ->shouldBe(null);
    }

    function it_returns_the_value_when_value_are_not_equals()
    {
        $original = ['scope' => 'mobile', 'data' => [['foo' => 'bar']]];

        $this->compare(['scope' => 'mobile', 'data' => [['foo' => 'baz']]], $original)
            ->shouldBe(['scope' => 'mobile', 'data' => [['foo' => 'baz']]]);

        $this->compare(['locale' => null, 'scope' => 'mobile', 'data' => [['foo' => 'baz']]], $original)
            ->shouldBe(['locale' => null, 'scope' => 'mobile', 'data' => [['foo' => 'baz']]]);
    }
}
