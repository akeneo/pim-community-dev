<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\StringValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class StringValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(StringValueUserIntentFactory::class);
    }

    function it_returns_set_text_user_intent()
    {
        $this->create(AttributeTypes::TEXT, 'a_text', [
            'data' => 'coucou',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetTextValue('a_text', null, null, 'coucou'));
    }

    function it_returns_set_text_area_user_intent()
    {
        $this->create(AttributeTypes::TEXTAREA, 'a_textarea', [
            'data' => '<p>coucou</p>',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetTextareaValue('a_textarea', null, null, '<p>coucou</p>'));
    }

    function it_returns_set_simple_select_user_intent()
    {
        $this->create(AttributeTypes::OPTION_SIMPLE_SELECT, 'a_simple_select', [
            'data' => 'coucou',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetSimpleSelectValue('a_simple_select', null, null, 'coucou'));
    }

    function it_returns_set_simple_reference_entity_user_intent()
    {
        $this->create(AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT, 'a_simple_reference_entity', [
            'data' => 'coucou',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetSimpleReferenceEntityValue('a_simple_reference_entity', null, null, 'coucou'));
    }

    function it_returns_set_simple_reference_data_user_intent()
    {
        $this->create(AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT, 'a_simple_reference_data', [
            'data' => 'coucou',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetSimpleReferenceDataValue('a_simple_reference_data', null, null, 'coucou'));
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
