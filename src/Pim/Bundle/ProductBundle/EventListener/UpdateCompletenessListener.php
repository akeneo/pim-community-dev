<?php

namespace Pim\Bundle\ProductBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Pim\Bundle\ProductBundle\Entity\Channel;
use Pim\Bundle\ProductBundle\Entity\Locale;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\AttributeRequirement;

/**
 * Aims to mark product completeness as to be re-calculated
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class UpdateCompletenessListener implements EventSubscriber
{
    /**
     * New channels, all related completeness must be re-calculated
     * @var Channel[]
     */
    protected $newChannels = array();

    /**
     * Added locales on a channel, all related completeness must be re-calculated
     * @var mixed[]
     */
    protected $newLocalesPerChannel = array();

    /**
     * Updated families, all related completeness must be re-calculated
     * @var Family[]
     */
    protected $updatedFamilies = array();

    /**
     * Specifies the list of events to listen
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array('postPersist', 'onFlush', 'postFlush');
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Channel) {
            $this->newChannels[]= $entity;
        }
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledCollectionUpdates() as $entity) {
            $this->addLocaleToAChannel($entity);
            $this->changeRequirementOfAFamily($entity);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->hasChanged()) {
            $em = $args->getEntityManager();

            // TODO
        }
    }

    /**
     * Check if some completeness must be re-calculated
     *
     * @return boolean
     */
    protected function hasChanged()
    {
        return (!empty($this->newChannels) or !empty($this->newLocalesPerChannel) or !empty($this->updatedFamilies));
    }

    /**
     * Check if a locale has been added to a channel
     */
    protected function addLocaleToAChannel($collection)
    {
        if ($entity->getOwner() instanceof Channel and $entity->first() instanceof Locale) {
            $channel = $entity->getOwner();
            $this->newLocales[$channel->getId()]= array();
            foreach ($entity->->getInsertDiff() as $locale) {
                $this->newLocales[$channel->getId()][]= $locale;
            }
        }
    }

    /**
     * Check if a attribute requirement has been changed on a family
     */
    protected function changeRequirementOfAFamily($collection)
    {
        if ($entity->getOwner() instanceof Family and $entity->first() instanceof AttributeRequirement) {
            $family = $entity->getOwner();
            $this->updatedFamilies[]= $family
        }
    }
}
