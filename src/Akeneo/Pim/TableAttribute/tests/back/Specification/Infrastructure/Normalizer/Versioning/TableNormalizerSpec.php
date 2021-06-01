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

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Versioning;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Versioning\TableNormalizer;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use PhpSpec\ObjectBehavior;

class TableNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TableNormalizer::class);
    }

    function it_only_supports_a_table_value_in_flat_or_versioning_format()
    {
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'flat')->shouldBe(true);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'versioning')->shouldBe(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldBe(false);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'other')->shouldBe(false);
    }

    function it_normalizes_a_table()
    {
        $this->normalize(Table::fromNormalized([['foo' => 'bar']]))->shouldBe([
            ['foo' => 'bar'],
        ]);
    }
}
