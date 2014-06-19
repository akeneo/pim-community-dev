<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\MongoDB\Collection
 */
class EnsureIndexesSubscriberSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $managerRegistry,
        DocumentManager $documentManager,
        Collection $collection
    ) {
        $managerRegistry->getManagerForClass('Product')->willReturn($documentManager);
        $documentManager->getDocumentCollection('Product')->willReturn($collection);

        $this->beConstructedWith(
            $managerRegistry,
            'Product',
            'Channel',
            'Locale',
            'Currency',
            'Attribute'
        );
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_doctrine_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['postPersist', 'postUpdate', 'postRemove']);
    }

    function it_generates_indexes_for_unique_attribute_insert()
    {
    }

    function it_generates_indexes_for_unique_attribute_update()
    {
    }

    function it_generates_indexes_for_identifier_attribute_insert()
    {
    }

    function it_generates_indexes_for_identifier_attribute_update()
    {
    }

    function it_generates_indexes_for_filterable_attribute_insert()
    {
    }

    function it_generates_indexes_for_filterable_attribute_update()
    {
    }

    function it_does_not_generates_indexes_for_other_attribute_insert()
    {
    }

    function it_does_not_generates_indexes_for_other_attribute_update()
    {
    }

    function it_generates_indexes_for_channel_insert()
    {
    }

    function it_generates_indexes_for_channel_update()
    {
    }

    function it_generates_indexes_for_locale_when_enabling_it()
    {
    }

    function it_removes_indexes_for_locale_when_disabling_it()
    {
    }

    function it_generates_indexes_for_currency_when_enablig_it()
    {
    }

    function it_removes_indexes_for_currency_when_disabling_it()
    {
    }
}
