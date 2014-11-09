<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

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

    function it_is_a_updater()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\UpdaterInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\RemoverInterface');
    }

    function it_throws_exception_when_update_anything_else_than_a_attribute_option()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a AttributeOption, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringUpdate($anythingElse);
    }

    function it_throws_exception_when_remove_anything_else_than_a_attribute_option()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a AttributeOption, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
