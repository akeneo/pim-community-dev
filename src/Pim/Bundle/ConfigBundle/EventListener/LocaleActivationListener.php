<?php

namespace Pim\Bundle\ConfigBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Aims to activate / deactivate locales from channels
 * See the postPersist method to have the details
 * This allows to always have synchronized locales
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleActivationListener implements EventSubscriber
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Inject service container
     *
     * @param ContainerInterface $container
     *
     * @return \Pim\Bundle\ConfigBundle\EventListener\LocaleActivationListener
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'postPersist'
        );
    }

    /**
     * After persist a channel
     * - Activate the deactivated locales linked to a channel
     * - Deactivate the activated locales no more linked to a channel
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Channel) {
            $this->activateLocales();

            $this->deactivateLocales();

            $this->getEntityManager()->flush();
        }
    }

    /**
     * Activate the deactivated locales linked to a channel
     *
     * Get entities and persist them allow us to call events if needed
     */
    protected function activateLocales()
    {
        $qb = $this->getLocaleRepository()->createQueryBuilder('l');
        $qb
            ->leftJoin('l.channels', 'c')
            ->andWhere('l.activated = false')
            ->andWhere('c.id IS NOT NULL');

        $locales = $qb->getQuery()->getResult();

        foreach ($locales as $locale) {
            $locale->setActivated(true);
            $this->getEntityManager()->persist($locale);
        }
    }

    /**
     * Deactivate the activated locales no more linked to a channel
     *
     * Get entities and persist them allow us to call events if needed
     */
    protected function deactivateLocales()
    {
        $qb = $this->getLocaleRepository()->createQueryBuilder('l');
        $qb
            ->leftJoin('l.channels', 'c')
            ->andWhere('l.activated = true')
            ->andWhere('c.id IS NULL');
        $locales = $qb->getQuery()->getResult();

        foreach ($locales as $locale) {
            $locale->setActivated(false);
            $this->getEntityManager()->persist($locale);
        }
    }

    /**
     * @return \Pim\Bundle\ConfigBundle\Entity\Repository\LocaleRepository
     */
    protected function getLocaleRepository()
    {
        return $this->getEntityManager()->getRepository('PimConfigBundle:Locale');
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
