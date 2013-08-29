<?php

namespace Pim\Bundle\ProductBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Pim\Bundle\ProductBundle\Entity\Channel;
use Pim\Bundle\ProductBundle\Entity\Locale;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\AttributeRequirement;
use Pim\Bundle\ProductBundle\Entity\PendingCompleteness;

/**
 * Aims to mark product completeness as to be re-calculated
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return array('postPersist', 'postUpdate', 'onFlush', 'postFlush');
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Channel) {
            $this->addChannel($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof AttributeRequirement) {
            $this->updateRequirement($entity);
        }
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $this->addLocaleToAChannel($collection);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->hasChanged()) {
            $em = $args->getEntityManager();
            $this->addPendingForChannels($em);
            $this->addPendingForLocales($em);
            $this->addPendingForFamilies($em);
            $em->flush();
        }
    }

    /**
     * Check if some completeness must be re-calculated
     *
     * @return boolean
     */
    public function hasChanged()
    {
        return (!empty($this->newChannels) or !empty($this->newLocalesPerChannel) or !empty($this->updatedFamilies));
    }

    /**
     * Check if a new channel has been added
     *
     * @param Channel $channel
     */
    protected function addChannel(Channel $channel)
    {
        if (!in_array($channel, $this->newChannels)) {
            $this->newChannels[]= $channel;
        }
    }

    /**
     * Check if a attribute requirement has been changed on a family
     *
     * @param object $entity
     */
    protected function updateRequirement(AttributeRequirement $requirement)
    {
        if ($requirement->getFamily() and !in_array($requirement->getFamily(), $this->updatedFamilies)) {
            $this->updatedFamilies[]= $requirement->getFamily();
        }
    }

    /**
     * Check if a locale has been added to a channel
     *
     * @param Collection $collection
     */
    protected function addLocaleToAChannel(Collection $collection)
    {
        if ($collection->getOwner() instanceof Channel and $collection->first() instanceof Locale) {
            $channel = $collection->getOwner();
            foreach ($collection->getInsertDiff() as $locale) {
                $this->newLocalesPerChannel[]= array('channel' => $channel, 'locale' => $locale);
            }
        }
    }

    /**
     * Add pending completeness for channel
     * @param EntityManager $em
     */
    protected function addPendingForChannels(EntityManager $em)
    {
        foreach ($this->newChannels as $channel) {
            $pending = $em->getRepository('PimProductBundle:PendingCompleteness')->findOneBy(
                array('channel' => $channel->getId(), 'locale' => null, 'family' => null)
            );
            if (!$pending) {
                $pending = new PendingCompleteness();
                $pending->setChannel($channel);
                $em->persist($pending);
            }
        }
        $this->newChannels = array();
    }

    /**
     * Add pending completeness for locales
     * @param EntityManager $em
     */
    protected function addPendingForLocales(EntityManager $em)
    {
        foreach ($this->newLocalesPerChannel as $data) {
            $channel = $data['channel'];
            $locale = $data['locale'];
            $pending = $em->getRepository('PimProductBundle:PendingCompleteness')->findOneBy(
                array('channel' => $channel->getId(), 'locale' => $locale->getId(), 'family' => null)
            );
            if (!$pending) {
                $pending = new PendingCompleteness();
                $pending->setChannel($channel);
                $pending->setLocale($locale);
                $em->persist($pending);
            }
        }
        $this->newLocalesPerChannel = array();
    }

    /**
     * Add pending completeness for locales
     * @param EntityManager $em
     */
    protected function addPendingForFamilies(EntityManager $em)
    {
        foreach ($this->updatedFamilies as $family) {
            $pending = $em->getRepository('PimProductBundle:PendingCompleteness')->findOneBy(
                array('channel' => null, 'locale' => null, 'family' => $family->getId())
            );
            if (!$pending) {
                $pending = new PendingCompleteness();
                $pending->setFamily($family);
                $em->persist($pending);
            }
        }
        $this->updatedFamilies = array();
    }
}
