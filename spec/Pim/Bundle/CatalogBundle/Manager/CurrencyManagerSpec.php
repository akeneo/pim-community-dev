<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CurrencyInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;

class CurrencyManagerSpec extends ObjectBehavior
{
    function let(
        CurrencyRepositoryInterface $repository,
        CurrencyInterface $eur,
        CurrencyInterface $usd,
        CurrencyInterface $gbp
    ) {
        $this->beConstructedWith($repository);
        $repository->findBy(array('activated' => true))->willReturn([$eur, $usd]);
        $repository->findBy(array())->willReturn([$eur, $usd, $gbp]);
        $repository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $eur->getCode()->willReturn('EUR');
        $usd->getCode()->willReturn('USD');
        $gbp->getCode()->willReturn('GBP');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\CurrencyManager');
    }

    function it_provides_active_currency_codes()
    {
        $this->getActiveCodes()->shouldReturn(['EUR', 'USD']);
    }
}
