<?php

declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PhpSpec\ObjectBehavior;

final class RecordsComparatorSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['akeneo_reference_entity_collection']);
    }

    public function it_is_a_comparator(): void
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    public function it_supports_reference_entity_collection_type(): void
    {
        $this->supports('akeneo_reference_entity_collection')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    public function it_get_changes_when_adding_records_data(): void
    {
        $changes = ['data' => ['redchilli', 'bluestorm'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn([
            'data' => ['redchilli', 'bluestorm'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    public function it_get_changes_when_changing_records_data(): void
    {
        $changes = ['data' => ['redchilli', 'bluestorm'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['bluestorm', 'navyblue', 'redchilli']];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => ['redchilli', 'bluestorm'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    public function it_returns_null_when_records_is_the_same(): void
    {
        $changes = ['data' => ['redchilli', 'bluestorm'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['bluestorm', 'redchilli'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    public function it_compares_in_a_case_insensitive_way(): void
    {
        $changes = ['data' => ['UPPER_CASE', 'lower_CASE'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['LOWER_case', 'upper_case'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    public function it_removes_duplicate_with_different_case_sensitivity(): void
    {
        $changes = ['data' => ['redchilli', 'bluestorm', 'RedChilli'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['bluestorm', 'redchilli'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    public function it_removes_old_duplicate_with_different_case_sensitivity(): void
    {
        $changes = ['data' => ['redchilli', 'bluestorm'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['RedChilli', 'bluestorm', 'redchilli'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => ['redchilli', 'bluestorm'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    public function it_handles_null_data_as_empty_array(): void
    {
        $changes = ['data' => null, 'locale' => null, 'scope' => null];
        $originals = ['data' => ['bluestorm'], 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => [],
            'locale' => null,
            'scope'  => null,
        ]);
    }

    public function it_handles_string_data_as_empty_array(): void
    {
        $changes = ['data' => '', 'locale' => null, 'scope' => null];
        $originals = ['data' => ['bluestorm'], 'locale' => null, 'scope' => null];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => [],
            'locale' => null,
            'scope'  => null,
        ]);
    }
}
