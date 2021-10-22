<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOption;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use PhpSpec\ObjectBehavior;

class SelectOptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'code' => 'sugar',
                'labels' => [
                    'en_US' => 'Sugar',
                    'fr_FR' => 'Sucre',
                ],
            ],
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelectOption::class);
    }

    function it_cannot_be_instantiated_without_a_code()
    {
        $this->beConstructedThrough('fromNormalized', [['labels' => ['en_US' => 'Sugar',]]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_a_non_string_code()
    {
        $this->beConstructedThrough('fromNormalized', [['code' => true, 'labels' => ['en_US' => 'Sugar',]]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_an_empty_code()
    {
        $this->beConstructedThrough('fromNormalized', [['code' => true, 'labels' => ['en_US' => 'Sugar',]]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_exposes_its_code()
    {
        $this->code()->shouldBeLike(SelectOptionCode::fromString('sugar'));
    }

    function it_exposes_its_labels()
    {
        $this->labels()->shouldBeLike(
            LabelCollection::fromNormalized(
                [
                    'en_US' => 'Sugar',
                    'fr_FR' => 'Sucre',
                ]
            )
        );
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn([
            'code' => 'sugar',
            'labels' => [
                'en_US' => 'Sugar',
                'fr_FR' => 'Sucre',
            ]
        ]);
    }
}
