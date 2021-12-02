<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\IsRequiredForCompleteness;
use PhpSpec\ObjectBehavior;

class SelectColumnSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'ingredient',
                    'labels' => ['en_US' => 'Ingredient', 'fr_FR' => 'Ingrédient'],
                    'options' => [['code' => 'sugar'], ['code' => 'salt']],
                ],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelectColumn::class);
    }

    function it_is_a_select_column()
    {
        $this->dataType()->shouldHaveType(ColumnDataType::class);
        $this->dataType()->asString()->shouldBe('select');
    }

    function it_has_a_code()
    {
        $this->code()->shouldHaveType(ColumnCode::class);
        $this->code()->asString()->shouldBe('ingredient');
    }

    function it_has_an_id()
    {
        $this->id()->shouldHaveType(ColumnId::class);
        $this->id()->asString()->shouldBe('ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224');
    }

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->normalize()->shouldReturn(['en_US' => 'Ingredient', 'fr_FR' => 'Ingrédient']);
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
                    'id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'ingredient',
                    'validations' => [],
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
                    'id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                    'code' => 'ingredient',
                    'validations' => [],
                ],
            ]
        );

        $this->validations()->shouldBeLike(ValidationCollection::createEmpty());
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldBeLike(
            [
                'id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                'code' => 'ingredient',
                'data_type' => 'select',
                'labels' => ['en_US' => 'Ingredient', 'fr_FR' => 'Ingrédient'],
                'validations' => (object)[],
                'is_required_for_completeness' => false,
            ]
        );
    }
}
