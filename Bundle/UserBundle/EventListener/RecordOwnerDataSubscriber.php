<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RecordOwnerDataSubscriber implements EventSubscriber
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(ContainerInterface $container)
    {
       $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
        );
    }

    /**
     * @return SecurityContextInterface
     */
    protected function getSecurityContext()
    {
        if (!$this->securityContext) {
            $this->securityContext = $this->container->get('security.context');
        }

        return $this->securityContext;
    }

    /**
     * Handle prePersist.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $user = $this->getSecurityContext()->getToken();
        if ($user) {
            $entity = $args->getEntity();
            $entity->setUser($user)
                ->setBusinessUnit($user->getBusinnesUnit())
                ->setOrganization($user->getOrganization());
        }
    }
}
