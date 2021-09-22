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
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TableNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldHaveType(TableNormalizer::class);
    }

    function it_only_supports_a_table_value_in_flat_or_versioning_format()
    {
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'flat')->shouldBe(true);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'versioning')->shouldBe(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldBe(false);
        $this->supportsNormalization(Table::fromNormalized([['foo' => 'bar']]), 'other')->shouldBe(false);
    }

    function it_throws_an_exception_when_the_field_name_is_not_in_the_context()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'normalize',
            [
                Table::fromNormalized([['foo' => 'bar']]),
                'flat',
                []
            ]
        );
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

        $this->normalize($table, 'flat', ['field_name' => 'nutritional_info'])
             ->shouldBe(['nutritional_info' => '[{"ingre_dient":"sugar","quantity":50},{"ingre_dient":"pepper","quantity":10}]']);
    }

    function it_normalizes_an_empty_table_to_null()
    {
        // Note: this cannot happen in a real use case, because such a table object would be invalid as a product value
        $table = Table::fromNormalized([['foo' => '', 'bar' => null], ['baz' => '']]); // $table->normalize() === [[], []];

        $this->normalize($table, 'flat', ['field_name' => 'nutritional_info'])
             ->shouldBe(['nutritional_info' => null]);
    }
}
