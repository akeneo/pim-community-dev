<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use PhpSpec\ObjectBehavior;

class TextColumnSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [['code' => 'ingredients', 'labels' => ['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients']]]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextColumn::class);
    }

    function it_is_a_text_column()
    {
        $this->dataType()->shouldHaveType(ColumnDataType::class);
        $this->dataType()->asString()->shouldBe('text');
    }

    function it_has_a_code()
    {
        $this->code()->shouldHaveType(ColumnCode::class);
        $this->code()->asString()->shouldBe('ingredients');
    }

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->labels()->shouldReturn(['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients']);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldBeLike(
            [
                'data_type' => 'text',
                'code' => 'ingredients',
                'labels' => ['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients'],
                'validations' => (object)[],
            ]
        );
    }
}
