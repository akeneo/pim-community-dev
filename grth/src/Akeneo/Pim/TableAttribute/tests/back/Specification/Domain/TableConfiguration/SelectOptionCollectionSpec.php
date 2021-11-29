<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOption;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use PhpSpec\ObjectBehavior;

class SelectOptionCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                ['code' => 'sugar'],
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
            ]
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelectOptionCollection::class);
        $this->shouldImplement(\IteratorAggregate::class);
    }

    function it_can_be_instantiated_with_no_option()
    {
        $this->beConstructedThrough('empty');
        $this->normalize()->shouldBeLike([]);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldBeLike(
            [
                ['code' => 'sugar', 'labels' => (object)[]],
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
            ]
        );
    }

    function it_returns_option_codes()
    {
        $this->getOptionCodes()
            ->shouldBeLike([
                SelectOptionCode::fromString('sugar'),
                SelectOptionCode::fromString('salt'),
            ]);
    }

    function it_gets_an_option_by_code()
    {
        $this->getByCode('sugar')->shouldBeAnInstanceOf(SelectOption::class);
        $this->getByCode('salt')->shouldBeAnInstanceOf(SelectOption::class);
        $this->getByCode('unknown')->shouldReturn(null);
    }

    function it_cannot_contain_duplicated_codes_with_case_insensitive()
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                ['code' => 'sugar'],
                ['code' => 'salt', 'labels' => ['fr_FR' => 'Sel']],
                ['code' => 'SUGAR', 'labels' => ['en_US' => 'sugar']],
                ['code' => 'SALT', 'labels' => ['en_US' => 'salt']],
            ]
        ]);

        $this->normalize()->shouldBeLike(
            [
                ['code' => 'SUGAR', 'labels' => ['en_US' => 'sugar']],
                ['code' => 'SALT', 'labels' => ['en_US' => 'salt']],
            ]
        );
    }
}
