<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttributeOptionManagerSpec extends ObjectBehavior
{
    const OPTION_CLASS    = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
    const OPT_VALUE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue';

    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $objectManager,
            $eventDispatcher,
            self::OPTION_CLASS,
            self::OPT_VALUE_CLASS
        );
    }
}
