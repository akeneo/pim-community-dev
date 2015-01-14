<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface;

class CopierRegistrySpec extends ObjectBehavior
{
    function it_gets_copier(
        AttributeInterface $fromAttribute1,
        AttributeInterface $fromAttribute2,
        AttributeInterface $toAttribute1,
        AttributeInterface $toAttribute2,
        AttributeInterface $toAttribute3,
        CopierInterface $copier1,
        CopierInterface $copier2
    ) {
        $fromAttribute2->getCode()->willReturn('fromAttribute2Code');
        $toAttribute3->getCode()->willReturn('toAttribute3Code');

        $copier1->supports($fromAttribute1, $toAttribute1)->willReturn(true);
        $copier1->supports($fromAttribute2, $toAttribute3)->willReturn(false);
        $copier1->supports($fromAttribute2, $toAttribute2)->willReturn(false);
        $copier2->supports($fromAttribute2, $toAttribute2)->willReturn(true);
        $copier2->supports($fromAttribute2, $toAttribute3)->willReturn(false);

        $this->register($copier1);
        $this->register($copier2);

        $this->get($fromAttribute1, $toAttribute1)->shouldReturn($copier1);
        $this->get($fromAttribute2, $toAttribute2)->shouldReturn($copier2);
        $this->shouldThrow(
            new \LogicException(
                'Source and destination attributes "fromAttribute2Code" and "toAttribute3Code" are not supported by any copier'
            ))->during('get', [$fromAttribute2, $toAttribute3]);
    }
}
