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

    function it_cannot_be_instantiated_with_invalid_data()
    {
        $this->beConstructedThrough('fromNormalized', [['sugar', 'salt']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
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
}
