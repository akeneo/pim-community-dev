<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\EventListener;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Pim\Bundle\FlexibleEntityBundle\Entity\Collection;

/**
 * Collection type subscriber
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CollectionTypeSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_BIND     => 'postBind',
            FormEvents::PRE_SET_DATA  => 'preSet',
        ];
    }

    /**
     * Pre set empty collection elements
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $data = $event->getData();

        if ($data instanceof AbstractEntityFlexibleValue) {
            /** @var ArrayCollection $collection */
            $collection = $data->getCollections();
            if ($collection->isEmpty()) {
                $collection->add(new Collection());
            }
        }
    }

    /**
     * Removes empty collection elements
     *
     * @param FormEvent $event
     */
    public function postBind(FormEvent $event)
    {
        $data = $event->getData();

        if ($data instanceof AbstractEntityFlexibleValue) {
            /** @var ArrayCollection $collection */
            $collection = $data->getCollections();
            foreach ($collection as $item) {
                if ($item == null || $item->__toString() == '') {
                    $collection->removeElement($item);
                }
            }
        }
    }
}
