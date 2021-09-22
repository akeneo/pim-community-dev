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

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Standard;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Standard\TableNormalizer;
use PhpSpec\ObjectBehavior;

class TableNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TableNormalizer::class);
    }

    function it_only_supports_a_table_in_storage_format()
    {
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'standard')->shouldBe(true);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'versioning')->shouldBe(false);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'storage')->shouldBe(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldBe(false);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'other')->shouldBe(false);
    }

    function it_normalizes_a_table()
    {
        $table = Table::fromNormalized([
            [
                'ingre_dient_9095d213-3188-4167-b551-cfcb75b285d1' => 'sugar',
                'quantity_4d06f549-348e-4769-8031-21203e149ebb' => 50,
            ],
            [
                'ingre_dient_a7fb7602-a993-4fff-a548-e60f21ab2a53' => 'pepper',
                'quantity_fe7dc246-4353-4209-858f-981b3a02f287' => 10,
            ],
        ]);

        $this->normalize($table)->shouldBe([
            ['ingre_dient' => 'sugar', 'quantity' => 50],
            ['ingre_dient' => 'pepper', 'quantity' => 10],
        ]);
    }
}
