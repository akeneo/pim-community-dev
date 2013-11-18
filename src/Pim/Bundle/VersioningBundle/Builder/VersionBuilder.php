<?php

namespace Pim\Bundle\VersioningBundle\Builder;

use Symfony\Component\Serializer\SerializerInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Version builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilder
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Build a version from a versionable entity
     *
     * @param object  $versionable
     * @param User    $user
     * @param integer $numVersion
     *
     * @return Version
     */
    public function buildVersion($versionable, User $user, $numVersion)
    {
        $resourceName = get_class($versionable);
        $resourceId   = $versionable->getId();
        // TODO: we don't use direct json serialize due to convert to audit data based on array_diff
        $data         = $this->serializer->normalize($versionable, 'csv', array('versioning' => true));

        return new Version($resourceName, $resourceId, $numVersion, $data, $user);
    }
}
