<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;

class OwnerTreeListener
{
    /**
     * Array with classes need to be checked for
     *
     * @var array
     */
    protected $securityClasses = [
        'Oro\Bundle\UserBundle\Entity\User',
        'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit',
        'Oro\Bundle\OrganizationBundle\Entity\Organization',
    ];

    /**
     * @var ServiceLink
     */
    protected $treeProvider;

    /**
     * @var bool
     */
    protected $needWarmap;

    /**
     * @param ServiceLink $treeProviderLink
     */
    public function __construct(ServiceLink $treeProviderLink)
    {
        $this->treeProviderLink = $treeProviderLink;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        $this->needWarmap = false;
        if ($this->checkEntities($uow->getScheduledEntityInsertions())) {
            $this->needWarmap = true;
        }
        if (!$this->needWarmap && $this->checkEntities($uow->getScheduledEntityUpdates())) {
            $this->needWarmap = true;
        }
        if (!$this->needWarmap && $this->checkEntities($uow->getScheduledEntityDeletions())) {
            $this->needWarmap = true;
        }

        if ($this->needWarmap) {
            $this->getTreeProvider()->clear();
        }
    }

    /**
     * @param array $entities
     * @return bool
     */
    protected function checkEntities(array $entities)
    {
        foreach ($entities as $entity) {
            if (in_array(get_class($entity), $this->securityClasses)) {

                return true;
            }
        }

        return false;
    }

    /**
     * @return OwnerTreeProvider
     */
    protected function getTreeProvider()
    {
        return $this->treeProviderLink->getService();
    }
}
