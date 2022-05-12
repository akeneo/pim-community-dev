<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\TextValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class TextValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TextValueUserIntentFactory::class);
    }

    function it_returns_set_text_user_intent()
    {
        $this->create(AttributeTypes::TEXT, 'a_text', [
            'data' => 'coucou',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetTextValue('a_text', null, null, 'coucou'));
    }

    function it_returns_clear_value()
    {
        $this->create(AttributeTypes::TEXT, 'a_text', [
            'data' => null,
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_text', 'ecommerce', 'fr_FR'));

        $this->create(AttributeTypes::TEXT, 'a_text', [
            'data' => '',
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_text', 'ecommerce', 'fr_FR'));
    }

    function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TEXT, 'a_text', ['value']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TEXT, 'a_text', ['data' => 'coucou', 'locale' => 'fr_FR']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TEXT, 'a_text', ['data' => 'coucou', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TEXT, 'a_text', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::TEXT, 'a_text', ['data' => [], 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);
    }
}
