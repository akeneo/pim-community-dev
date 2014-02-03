<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;

class CurrencyManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager)
    {
        $this->beConstructedWith($objectManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\CurrencyManager');
    }

    function it_provides_currencies(ObjectManager $objectManager, CurrencyRepository $repository)
    {
        $objectManager->getRepository('PimCatalogBundle:Currency')->willReturn($repository);
        $repository->findBy(array())->willReturn(array('EUR', 'USD'));
        $this->getCurrencies()->shouldBeArray();
        $this->getCurrencies()->shouldHaveCount(2);
    }
}
