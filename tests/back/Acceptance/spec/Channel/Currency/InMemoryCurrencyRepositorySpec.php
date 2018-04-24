<?php

namespace spec\Akeneo\Test\Acceptance\Channel\Currency;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Channel\Currency\InMemoryCurrencyRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Prophecy\Argument;

class InMemoryCurrencyRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryCurrencyRepository::class);
    }

    function it_is_a_identifiable_object_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_saves_a_role()
    {
        $this->save(new Currency())->shouldReturn(null);
    }

    function it_only_saves_currencies()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['wrong_object']);
    }

    function it_finds_a_currency_by_its_identifier()
    {
        $currency = new Currency();
        $currency->setCode('currency');
        $this->save($currency);
        $this->findOneByIdentifier('currency')->shouldReturn($currency);
    }

    function it_returns_null_if_the_currency_does_not_exist()
    {
        $this->findOneByIdentifier('currency')->shouldReturn(null);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }
}
