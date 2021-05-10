<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Config;

use Akeneo\Pim\TableAttribute\Domain\Config\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\Config\ValueObject\ColumnCode;
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
        $this->dataType()->shouldBe('text');
    }

    function it_has_a_code()
    {
        $this->code()->shouldHaveType(ColumnCode::class);
        $this->code()->asString()->shouldBe('ingredients');
    }

    function it_has_labels()
    {
        $this->labels()->shouldReturn(['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients']);
    }
}
