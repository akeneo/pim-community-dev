<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\IdentifierValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class IdentifierValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IdentifierValueUserIntentFactory::class);
    }

    function it_returns_set_identifier_user_intent()
    {
        $this->create(AttributeTypes::IDENTIFIER, 'my_identifier', ['data' => 'my_sku'])
            ->shouldBeLike(new SetIdentifierValue('my_identifier', 'my_sku'));
    }

    function it_throws_an_error_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::IDENTIFIER, 'my_identifier', 'coucou']);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::IDENTIFIER, 'my_identifier', ['data' => []]]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::IDENTIFIER, 'my_identifier', ['coucou']]);
    }
}
