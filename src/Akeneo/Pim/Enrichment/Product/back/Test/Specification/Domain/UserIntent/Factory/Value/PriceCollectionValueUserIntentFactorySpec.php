<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\PriceCollectionValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class PriceCollectionValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PriceCollectionValueUserIntentFactory::class);
    }

    function it_returns_set_price_collection_user_intent()
    {
        $this->create(AttributeTypes::PRICE_COLLECTION, 'a_price', [
            'data' => [
                ['amount' => 20, 'currency' => 'EUR'],
                ['amount' => 10, 'currency' => 'USD']
            ],
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetPriceCollectionValue(
            'a_price',
            null,
            null,
            [
                new PriceValue(20, 'EUR'),
                new PriceValue(10, 'USD'),
            ]
        ));
    }

    function it_returns_clear_value()
    {
        $this->create(AttributeTypes::PRICE_COLLECTION, 'a_price', [
            'data' => null,
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_price', 'ecommerce', 'fr_FR'));

        $this->create(AttributeTypes::PRICE_COLLECTION, 'a_price', [
            'data' => [],
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_price', 'ecommerce', 'fr_FR'));
    }

    function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['value']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['data' => 'coucou', 'locale' => 'fr_FR']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['data' => 'coucou', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['data' => ['coucou'], 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['data' => ['amount' => 20], 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['data' => ['unit' => 'KILOMETER'], 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);
    }
}
