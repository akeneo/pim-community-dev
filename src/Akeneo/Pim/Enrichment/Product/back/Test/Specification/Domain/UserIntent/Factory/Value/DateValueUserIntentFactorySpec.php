<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\DateValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class DateValueUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DateValueUserIntentFactory::class);
    }

    function it_returns_set_date_user_intent()
    {
        $this->create(AttributeTypes::DATE, 'a_date', [
            'data' => '2022-05-20',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetDateValue(
            'a_date',
            null,
            null,
            \DateTimeImmutable::createFromFormat('Y-m-d', '2022-05-20')
        ));

        $this->create(AttributeTypes::DATE, 'a_date', [
            'data' => '2022-05-20T00:00:00+00:00',
            'locale' => null,
            'scope' => null,
        ])->shouldBeLike(new SetDateValue(
            'a_date',
            null,
            null,
            \DateTimeImmutable::createFromFormat('Y-m-d', '2022-05-20')
        ));
    }

    function it_returns_clear_value()
    {
        $this->create(AttributeTypes::DATE, 'a_date', [
            'data' => null,
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_date', 'ecommerce', 'fr_FR'));

        $this->create(AttributeTypes::DATE, 'a_date', [
            'data' => '',
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
        ])->shouldBeLike(new ClearValue('a_date', 'ecommerce', 'fr_FR'));
    }

    function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::DATE, 'a_date', ['value']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::DATE, 'a_date', ['data' => 'coucou', 'locale' => 'fr_FR']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::DATE, 'a_date', ['data' => 'coucou', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::DATE, 'a_date', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', [AttributeTypes::DATE, 'a_date', ['data' => [], 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyException::class)
            ->during('create', [AttributeTypes::DATE, 'a_date', ['data' => 'je suis une date', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);

        $this->shouldThrow(InvalidPropertyException::class)
            ->during('create', [AttributeTypes::DATE, 'a_date', ['data' => '2020-20-20', 'locale' => 'fr_FR', 'scope' => 'ecommerce']]);
    }
}
