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

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Storage;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Storage\TableNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TableNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NormalizerInterface::class);
        $this->shouldHaveType(TableNormalizer::class);
    }

    function it_only_supports_a_table_in_storage_format()
    {
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'storage')->shouldBe(true);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'standard')->shouldBe(false);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'versioning')->shouldBe(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldBe(false);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'other')->shouldBe(false);
    }

    function it_normalizes_a_table()
    {
        $this->normalize(Table::fromNormalized([['foo_123456' => 'bar']]))->shouldBe([
            ['foo_123456' => 'bar'],
        ]);
    }
}
