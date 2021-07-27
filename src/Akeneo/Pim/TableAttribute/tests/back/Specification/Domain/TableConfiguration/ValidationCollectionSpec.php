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

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use PhpSpec\ObjectBehavior;

class ValidationCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedThrough('fromNormalized', [ColumnDataType::fromString('text'), ['max_length' => 100]]);
        $this->shouldHaveType(ValidationCollection::class);
    }

    function it_throws_an_exception_when_there_are_no_keys()
    {
        $this->beConstructedThrough('fromNormalized', [ColumnDataType::fromString('text'), ['something']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_keys_are_numbers()
    {
        $this->beConstructedThrough('fromNormalized', [ColumnDataType::fromString('text'), [12 => 'something']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_instantiated_with_a_stdclass()
    {
        $this->beConstructedThrough('fromNormalized', [ColumnDataType::fromString('text'), new \stdClass()]);

        $this->normalize()->shouldBeLike((object) []);
    }

    function it_normalizes_validations()
    {
        $this->beConstructedThrough('fromNormalized', [ColumnDataType::fromString('text'), ['max_length' => 255]]);

        $this->normalize()->shouldReturn(['max_length' => 255]);
    }

    function it_throws_an_exception_for_an_wrong_validation()
    {
        $this->beConstructedThrough('fromNormalized', [ColumnDataType::fromString('text'), ['min' => 5]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_normalizes_empty_validation()
    {
        $this->beConstructedThrough('createEmpty');

        $this->normalize()->shouldBeLike((object) []);
    }
}
