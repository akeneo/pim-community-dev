<?php

namespace Pim\Bundle\VersioningBundle\Manager;

use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Version builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionManager
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param ObjectManager       $em
     * @param SerializerInterface $serializer
     */
    public function __construct(ObjectManager $em, SerializerInterface $serializer)
    {
        $this->em           = $em;
        $this->serializer   = $serializer;
    }

    /**
     * @param EntityManager $em
     *
     * @return Version
     */
    public function getPreviousVersion(Version $version)
    {
        /** @var Version $version */
        $previous = $this->em->getRepository('PimVersioningBundle:Version')
            ->findOneBy(
                array('resourceId' => $version->getResourceId(), 'resourceName' => $version->getResourceName()),
                array('loggedAt' => 'desc')
            );

        return $previous;
    }
}
