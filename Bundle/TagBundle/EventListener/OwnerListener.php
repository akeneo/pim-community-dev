<?php

namespace Oro\Bundle\TagBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\TagBundle\Entity\ContainAuthorInterface;
use Oro\Bundle\TagBundle\Entity\ContainUpdaterInterface;

class OwnerListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Pre update event process
     *
     * @param PreUpdateEventArgs $args
     * @return $this
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $user = $this->getUser();

        if (!$entity instanceof ContainUpdaterInterface || !$user) {
            return $this;
        }

        $entity->setUpdatedBy($user);
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $meta = $em->getClassMetadata(get_class($entity));
        $uow->recomputeSingleEntityChangeSet($meta, $entity);

        return $this;
    }

    /**
     * Pre persist event process
     *
     * @param LifecycleEventArgs $args
     * @return $this
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $user = $this->getUser();
        if (!$entity instanceof ContainAuthorInterface || !$user) {
            return $this;
        }
        $entity->setCreatedBy($user);

        return $this;
    }

    /**
     * Return current user
     *
     * @return User|bool
     */
    private function getUser()
    {
        $securityContext = $this->container->get('security.context');

        if (!$securityContext) {
            return false;
        } else {
            return $securityContext->getToken() && !is_string($securityContext->getToken()->getUser())
                ? $securityContext->getToken()->getUser() : false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
