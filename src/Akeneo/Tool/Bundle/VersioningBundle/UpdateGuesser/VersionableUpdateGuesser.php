<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

/**
 * Fields update guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionableUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * Entities configured as versionable without implementing interface because coming
     * from third party bundles
     *
     * @var array
     */
    protected $versionableEntities;

    /**
     * Constructor
     *
     * @param array $versionableEntities
     */
    public function __construct(array $versionableEntities)
    {
        $this->versionableEntities = $versionableEntities;
    }

    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return $action === UpdateGuesserInterface::ACTION_UPDATE_ENTITY;
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];
        if ($entity instanceof VersionableInterface ||
            in_array(ClassUtils::getClass($entity), $this->versionableEntities)
        ) {
            $pendings[] = $entity;
        }

        return $pendings;
    }
}
