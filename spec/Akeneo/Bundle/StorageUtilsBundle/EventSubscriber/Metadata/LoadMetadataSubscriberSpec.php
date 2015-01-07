<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\Metadata;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoadMetadataSubscriberSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'Pim\Bundle\CatalogBundle\Model\ProductValue',
            [
                'values' => 'AppBundle\Model\ProductValue'
            ],
            'AppBundle\Repository\ProductRepository'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\Metadata\LoadMetadataSubscriber');
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_event()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            'loadClassMetadata'
        ));
    }

    function it_updates_doctrine_mapping(LoadClassMetadataEventArgs $eventArgs, ClassMetadataInfo $classMetadataInfo)
    {
        $eventArgs->getClassMetadata()->willReturn($classMetadataInfo);
        $classMetadataInfo->getName()->willReturn('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $classMetadataInfo->hasField('values')->shouldBeCalled();

        $this->loadClassMetadata($eventArgs);
    }
}
