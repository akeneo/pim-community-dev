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
        $this->normalize(Table::fromNormalized([['foo' => 'bar']]), 'flat', ['field_name' => 'nutritional_info'])
             ->shouldBe(['nutritional_info' => '[{"foo":"bar"}]']);
    }

    function it_normalizes_an_empty_table_to_null()
    {
        // Note: this cannot happen in a real use case, because such a table object would be invalid as a product value
        $table = Table::fromNormalized([['foo' => '', 'bar' => null], ['baz' => '']]); // $table->normalize() === [[], []];

        $this->normalize($table, 'flat', ['field_name' => 'nutritional_info'])
             ->shouldBe(['nutritional_info' => null]);
    }
}
