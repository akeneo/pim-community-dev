<?php

namespace spec\Akeneo\Test\Acceptance\Channel\Locale;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Channel\Locale\InMemoryLocaleRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Prophecy\Argument;

class InMemoryLocaleRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryLocaleRepository::class);
    }

    function it_is_a_identifiable_object_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_saves_a_locale()
    {
        $this->save(new Locale())->shouldReturn(null);
    }

    function it_only_saves_locales()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['wrong_object']);
    }

    function it_finds_a_locale_by_its_identifier()
    {
        $locale = new Locale();
        $locale->setCode('locale');
        $this->save($locale);
        $this->findOneByIdentifier('locale')->shouldReturn($locale);
    }

    function it_returns_null_if_the_locale_does_not_exist()
    {
        $this->findOneByIdentifier('locale')->shouldReturn(null);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }
}
