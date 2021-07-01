<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
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
            ->shouldReturn(['sugar', 'salt']);
    }

    function it_returns_true_false_if_it_contains_the_option_code_or_not()
    {
        $this->hasOptionCode('sugar')->shouldReturn(true);
        $this->hasOptionCode('salt')->shouldReturn(true);
        $this->hasOptionCode('unknown')->shouldReturn(false);
    }
}
