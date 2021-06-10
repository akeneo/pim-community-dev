<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use PhpSpec\ObjectBehavior;

class SelectColumnSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'code' => 'ingredient',
                    'labels' => ['en_US' => 'Ingredient', 'fr_FR' => 'Ingrédient'],
                ],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelectColumn::class);
    }

    function it_is_a_text_column()
    {
        $this->dataType()->shouldHaveType(ColumnDataType::class);
        $this->dataType()->asString()->shouldBe('select');
    }

    function it_has_a_code()
    {
        $this->code()->shouldHaveType(ColumnCode::class);
        $this->code()->asString()->shouldBe('ingredient');
    }

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->labels()->shouldReturn(['en_US' => 'Ingredient', 'fr_FR' => 'Ingrédient']);
    }
}
