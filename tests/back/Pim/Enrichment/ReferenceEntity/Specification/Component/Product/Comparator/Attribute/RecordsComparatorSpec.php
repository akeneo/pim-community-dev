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
    function let()
    {
        $this->beConstructedWith(['akeneo_reference_entity_collection']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    function it_supports_reference_entity_collection_type()
    {
        $this->supports('akeneo_reference_entity_collection')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_records_data()
    {
        $changes = ['data' => ['redchilli', 'bluestorm'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->compare($changes, $originals)->shouldReturn([
            'data' => ['redchilli', 'bluestorm'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    function it_get_changes_when_changing_records_data()
    {
        $changes = ['data' => ['redchilli', 'bluestorm'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['bluestorm', 'navyblue', 'redchilli']];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => ['redchilli', 'bluestorm'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }

    function it_returns_null_when_records_is_the_same()
    {
        $changes = ['data' => ['redchilli', 'bluestorm'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['bluestorm', 'redchilli'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_compares_in_a_case_insensitive_way()
    {
        $changes = ['data' => ['UPPER_CASE', 'lower_CASE'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['LOWER_case', 'upper_case'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_remove_duplicate_with_different_case_sensitivity()
    {
        $changes = ['data' => ['redchilli', 'bluestorm', 'RedChilli'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['bluestorm', 'redchilli'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_remove_old_duplicate_with_different_case_sensitivity()
    {
        $changes = ['data' => ['redchilli', 'bluestorm'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['data' => ['RedChilli', 'bluestorm', 'redchilli'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->compare($changes, $originals)->shouldReturn([
            'data'  => ['redchilli', 'bluestorm'],
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
    }
}
