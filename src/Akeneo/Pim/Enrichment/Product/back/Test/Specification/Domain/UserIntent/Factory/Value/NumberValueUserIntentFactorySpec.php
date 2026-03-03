<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\NumberValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class NumberValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NumberValueUserIntentFactory::class);
    }

    function it_returns_set_number_user_intent()
    {
        $this->create(AttributeTypes::NUMBER, 'a_number', [
            'data' => '10',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetNumberValue('a_number', null, null, '10'));

        $this->create(AttributeTypes::NUMBER, 'a_number', [
            'data' => '10.02',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetNumberValue('a_number', null, null, '10.02'));
    }

    function it_returns_clear_value()
    {
        $this->create(AttributeTypes::NUMBER, 'a_number', [
            'data' => null,
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_number', 'ecommerce', 'fr_FR'));

        $this->create(AttributeTypes::NUMBER, 'a_number', [
            'data' => '',
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_number', 'ecommerce', 'fr_FR'));
    }
}
