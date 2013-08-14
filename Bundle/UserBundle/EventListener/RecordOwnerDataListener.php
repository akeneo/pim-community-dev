<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;

class RecordOwnerDataListener
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
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param ContainerInterface $container
     * @param ConfigProvider $configProvider
     * @param ObjectManager $om
     */
    public function __construct(ContainerInterface $container, ConfigProvider $configProvider)
    {
        $this->container      = $container;
        $this->configProvider = $configProvider;
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
        $token = $this->getSecurityContext()->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user) {
                $ownerId = null;
                $post = $this->container->get('request')->request->all();
                if ($post) {
                    foreach ($post as $params) {
                        if (is_array($params)) {
                            foreach ($params as $key => $formParam) {
                                if ($key === 'owner') {
                                    $ownerId = $formParam;
                                }
                            }
                        }
                    }
                }
                $entity = $args->getEntity();
                if ($this->configProvider->hasConfig(get_class($entity))) {
                    /** @var $config EntityConfig */
                    $config = $this->configProvider->getConfig(get_class($entity));
                    $entityValues = $config->getValues();

                    $owner = null;
                    if (OwnershipType::OWNERSHIP_TYPE_USER == $entityValues['owner_type']) {
                        if ($ownerId) {
                            $owner = $args->getEntityManager()
                                ->getRepository('OroUserBundle:User')
                                ->find($ownerId);
                        } else {
                            $owner = $user;
                        }
                    } elseif (OwnershipType::OWNERSHIP_TYPE_BUSINESS_UNIT == $entityValues['owner_type']) {
                        if ($ownerId) {
                            $owner = $args->getEntityManager()
                                ->getRepository('OroOrganizationBundle:BusinessUnit')
                                ->find($ownerId);
                        } else {
                            $businessUnits = $user->getBusinessUnits();
                            $owner = $businessUnits->first();
                        }
                    } elseif (OwnershipType::OWNERSHIP_TYPE_ORGANIZATION == $entityValues['owner_type']) {
                        $businessUnits = $user->getBusinessUnits();
                        $owner = $businessUnits->first()->getOrganization();
                    }

                    if ($owner && method_exists($entity, 'setOwner')) {
                        $entity->setOwner($owner);
                    }
                }
            }
        }
    }
}
