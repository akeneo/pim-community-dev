<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\FileValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class FileValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FileValueUserIntentFactory::class);
    }

    function it_returns_set_file_user_intent()
    {
        $this->create(AttributeTypes::FILE, 'a_file', [
            'data' => 'coucou',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetFileValue('a_file', null, null, 'coucou'));
    }

    function it_returns_set_image_user_intent()
    {
        $this->create(AttributeTypes::IMAGE, 'an_image', [
            'data' => 'coucou',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetImageValue('an_image', null, null, 'coucou'));
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
