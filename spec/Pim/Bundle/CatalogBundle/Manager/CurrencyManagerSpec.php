<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Currency;

use Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;

class CurrencyManagerSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CurrencyRepository $repository,
        Currency $eur,
        Currency $usd,
        Currency $gbp

    ) {
        $this->beConstructedWith($objectManager);
        $objectManager->getRepository('PimCatalogBundle:Currency')->willReturn($repository);

        $repository->findBy(array('activated' => true))->willReturn([$eur, $usd]);
        $repository->findBy(array())->willReturn([$eur, $usd, $gbp]);

        $eur->getCode()->willReturn('EUR');
        $usd->getCode()->willReturn('USD');
        $gbp->getCode()->willReturn('GBP');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\CurrencyManager');
    }

    function it_provides_active_currencies($eur, $usd)
    {
        $this->getActiveCurrencies()->shouldReturn([$eur, $usd]);
    }

    function it_provides_currencies($repository, $eur, $usd, $gbp)
    {
        $this->getCurrencies()->shouldReturn([$eur, $usd, $gbp]);

        $criterias = ['foo' => 'bar'];
        $repository->findBy($criterias)->willReturn([$eur, $gbp]);
        $this->getCurrencies($criterias)->shouldReturn([$eur, $gbp]);
    }

    function it_provides_active_currency_codes()
    {
        $this->getActiveCodes()->shouldReturn(['EUR', 'USD']);
    }

    function it_provides_active_currency_code_choices()
    {
        $this->getActiveCodeChoices()->shouldReturn(['EUR' => 'EUR', 'USD' => 'USD']);
    }
}
