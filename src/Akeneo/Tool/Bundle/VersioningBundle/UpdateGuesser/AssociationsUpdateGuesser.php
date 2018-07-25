<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Doctrine\ORM\EntityManager;

/**
 * Guess for associations updates and add the owner product entity to pendings versionnning if needed
 *
 * @author    jm leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationsUpdateGuesser implements UpdateGuesserInterface
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
        if ($entity instanceof AssociationInterface) {
            $pendings[] = $entity->getOwner();
        }

        return $pendings;
    }
}
