<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use PhpSpec\ObjectBehavior;

class NumberColumnSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [['code' => 'quantities', 'labels' => ['en_US' => 'Quantities', 'fr_FR' => 'Quantités']]]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NumberColumn::class);
    }

    function it_is_a_text_column()
    {
        $this->dataType()->shouldHaveType(ColumnDataType::class);
        $this->dataType()->asString()->shouldBe('number');
    }

    function it_has_a_code()
    {
        $this->code()->shouldHaveType(ColumnCode::class);
        $this->code()->asString()->shouldBe('quantities');
    }

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->normalize()->shouldReturn(['en_US' => 'Quantities', 'fr_FR' => 'Quantités']);
    }

    function it_returns_the_validations()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'code' => 'number',
                    'validations' => ['min' => 5],
                ],
            ]
        );

        $this->validations()->shouldBeLike(ValidationCollection::fromNormalized(
            ColumnDataType::fromString('number'),
            ['min' => 5]
        ));
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldBeLike(
            [
                'data_type' => 'number',
                'code' => 'quantities',
                'labels' => ['en_US' => 'Quantities', 'fr_FR' => 'Quantités'],
                'validations' => (object)[],
            ]
        );
    }
}
