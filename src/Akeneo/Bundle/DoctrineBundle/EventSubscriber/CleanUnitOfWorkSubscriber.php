<?php

namespace Akeneo\Bundle\DoctrineBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Akeneo Doctrine event
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanUnitOfWorkSubscriber implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return ['postFlush'];
    }

    /**
     * Manually clears doctrine's UnitOfWork object after flush.
     *
     * @param OnFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $reflector = new \ReflectionClass($uow);
        $scheduledForCheck = $reflector->getProperty('scheduledForDirtyCheck');
        $scheduledForCheck->setAccessible(true);
        $scheduledForCheck->setValue($uow, []);
        $scheduledForCheck->setAccessible(false);
    }
}
