<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\IsRequiredForCompleteness;
use PhpSpec\ObjectBehavior;

class TextColumnSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'id' => 'ingredients_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'ingredients',
                    'labels' => ['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients'],
                ],
            ]
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

    function it_has_an_id()
    {
        $this->id()->shouldHaveType(ColumnId::class);
        $this->id()->asString()->shouldBe('ingredients_cf30d88f-38c9-4c01-9821-4b39a5e3c224');
    }

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->normalize()->shouldReturn(['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients']);
    }

    function it_is_not_required_for_completeness()
    {
        $this->isRequiredForCompleteness()->shouldHaveType(IsRequiredForCompleteness::class);
        $this->isRequiredForCompleteness()->asBoolean()->shouldReturn(false);
    }

    function it_is_required_for_completeness()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'id' => 'ingredients_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'a_text',
                    'validations' => ['max_length' => 50],
                    'is_required_for_completeness' => true,
                ],
            ]
        );

        $this->isRequiredForCompleteness()->shouldHaveType(IsRequiredForCompleteness::class);
        $this->isRequiredForCompleteness()->asBoolean()->shouldReturn(true);
    }

    function it_returns_the_validations()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'id' => 'ingredients_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'a_text',
                    'validations' => ['max_length' => 50],
                ],
            ]
        );

        $this->validations()->shouldBeLike(ValidationCollection::fromNormalized(
            ColumnDataType::fromString('text'),
            ['max_length' => 50]
        ));
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldBeLike(
            [
                'id' => 'ingredients_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                'data_type' => 'text',
                'code' => 'ingredients',
                'labels' => ['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients'],
                'validations' => (object)[],
                'is_required_for_completeness' => false,
            ]
        );
    }
}
