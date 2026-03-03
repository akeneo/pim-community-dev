<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\MeasurementValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class MeasurementValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementValueUserIntentFactory::class);
    }

    function it_returns_set_measurement_user_intent()
    {
        $this->create(AttributeTypes::METRIC, 'a_metric', [
            'data' => [
                'amount' => 20,
                'unit' => 'KILOMETER'
            ],
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetMeasurementValue('a_metric', null, null, 20, 'KILOMETER'));
    }

    function it_returns_clear_value()
    {
        $this->create(AttributeTypes::METRIC, 'a_metric', [
            'data' => null,
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_metric', 'ecommerce', 'fr_FR'));

        $this->create(AttributeTypes::METRIC, 'a_metric', [
            'data' => '',
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_metric', 'ecommerce', 'fr_FR'));

        $this->create(AttributeTypes::METRIC, 'a_metric', [
            'data' => ['amount' => null, 'unit' => 'KILOMETER'],
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_metric', 'ecommerce', 'fr_FR'));

        $this->create(AttributeTypes::METRIC, 'a_metric', [
            'data' => ['amount' => '', 'unit' => 'KILOMETER'],
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_metric', 'ecommerce', 'fr_FR'));
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
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['data' => [], 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['data' => ['amount' => 20], 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::METRIC, 'a_metric', ['data' => ['unit' => 'KILOMETER'], 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);
    }
}
