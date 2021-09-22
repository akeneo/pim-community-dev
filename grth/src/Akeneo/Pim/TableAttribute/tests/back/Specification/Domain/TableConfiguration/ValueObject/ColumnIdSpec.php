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

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use PhpSpec\ObjectBehavior;

class ColumnIdSpec extends ObjectBehavior
{
    function it_can_be_instantiated_from_a_string()
    {
        $this->beConstructedThrough('fromString', ['ingredient_ffcaf89a-18ee-4607-b9cd-7812618dcc08']);
        $this->shouldHaveType(ColumnId::class);
    }

    function it_throws_an_error_when_id_is_empty()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_error_when_id_is_malformed()
    {
        $this->beConstructedThrough('fromString', ['malformed_id']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_instantiated_from_a_column_code()
    {
        $this->beConstructedThrough('fromColumnCode', [
            ColumnCode::fromString('ingredient_2'),
            'ffcaf89a-18ee-4607-b9cd-7812618dcc08',
        ]);
    }

    function it_can_be_displayed_as_a_string()
    {
        $this->beConstructedThrough('fromString', ['ingredient_ffcaf89a-18ee-4607-b9cd-7812618dcc08']);
        $this->asString()->shouldBe('ingredient_ffcaf89a-18ee-4607-b9cd-7812618dcc08');
    }

    function it_can_extract_the_column_code()
    {
        $this->beConstructedThrough('fromString', ['main_ingredient_ffcaf89a-18ee-4607-b9cd-7812618dcc08']);
        $this->extractColumnCode()->shouldBeLike(ColumnCode::fromString('main_ingredient'));
    }

    function it_can_compare_to_another_column_id()
    {
        $this->beConstructedThrough('fromString', ['ingredient_ffcaf89a-18ee-4607-b9cd-7812618dcc08']);

        $this->equals(ColumnId::fromString('ingredient_ffcaf89a-18ee-4607-b9cd-7812618dcc08'))->shouldBe(true);
        $this->equals(ColumnId::fromString('quantity_ffcaf89a-18ee-4607-b9cd-7812618dcc08'))->shouldBe(false);
        $this->equals(ColumnId::fromString('ingredient_5156ccca-6a33-4c44-806f-b896b730df8d'))->shouldBe(false);
    }
}
