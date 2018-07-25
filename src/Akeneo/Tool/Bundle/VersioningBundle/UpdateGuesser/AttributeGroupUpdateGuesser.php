<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\ORM\EntityManager;

/**
 * Change attribute group update guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupUpdateGuesser implements UpdateGuesserInterface
{
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
        if ($entity instanceof AttributeInterface) {
            $pendings[] = $entity;
            $changeset = $em->getUnitOfWork()->getEntityChangeSet($entity);
            if ($changeset && in_array('group', array_keys($changeset))) {
                $groupChangeset = $changeset['group'];
                if (isset($groupChangeset[0]) && $groupChangeset[0]) {
                    $pendings[] = $groupChangeset[0];
                }
                if (isset($groupChangeset[1]) && $groupChangeset[1]) {
                    $pendings[] = $groupChangeset[1];
                }
            }
        }

        return $pendings;
    }
}
