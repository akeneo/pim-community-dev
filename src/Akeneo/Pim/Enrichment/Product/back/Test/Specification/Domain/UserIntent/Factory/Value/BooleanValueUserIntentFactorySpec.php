<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\BooleanValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class BooleanValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BooleanValueUserIntentFactory::class);
    }

    function it_returns_set_boolean_user_intent()
    {
        $this->create(AttributeTypes::BOOLEAN, 'a_bool', [
            'data' => true,
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetBooleanValue('a_bool', null, null, true));
    }

    function it_returns_clear_value()
    {
        $this->create(AttributeTypes::BOOLEAN, 'a_bool', [
            'data' => null,
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_bool', 'ecommerce', 'fr_FR'));

        $this->create(AttributeTypes::BOOLEAN, 'a_bool', [
            'data' => '',
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_bool', 'ecommerce', 'fr_FR'));
    }

    function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::BOOLEAN, 'a_bool', ['value']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::BOOLEAN, 'a_bool', ['data' => 'coucou', 'locale' => 'fr_FR']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::BOOLEAN, 'a_bool', ['data' => 'coucou', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::BOOLEAN, 'a_bool', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::BOOLEAN, 'a_bool', ['data' => 'je suis false', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);
    }
}
