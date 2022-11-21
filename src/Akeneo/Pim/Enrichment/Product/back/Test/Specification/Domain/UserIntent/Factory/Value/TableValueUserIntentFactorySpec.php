<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTableValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\TableValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class TableValueUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(TableValueUserIntentFactory::class);
    }

    public function it_returns_set_table_user_intent()
    {
        $this->create(AttributeTypes::TABLE, 'a_table', [
            'data' => [['average_nutritional_value' => 'carbohydrate', 'per_100_g' => '100']],
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetTableValue('a_table', null, null, [['average_nutritional_value' => 'carbohydrate', 'per_100_g' => '100']]));
    }

    public function it_returns_clear_value_user_intent()
    {
        $this->create(AttributeTypes::TABLE, 'a_table', [
            'data' => null,
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new ClearValue('a_table', null, null));
    }

    public function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TABLE, 'a_table', ['value']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TABLE, 'a_table', ['data' => 'coucou', 'locale' => 'fr_FR']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TABLE, 'a_table', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TABLE, 'a_table', ['data' => 'coucou', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);
    }
}
