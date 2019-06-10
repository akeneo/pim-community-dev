<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

/**
 * Guess update on family attribute requirements.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyAttributeRequirementUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return $action === UpdateGuesserInterface::ACTION_UPDATE_ENTITY
            || $action === UpdateGuesserInterface::ACTION_DELETE;
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $updatedEntities = [];

        if ($entity instanceof AttributeRequirement) {
            $family = $entity->getFamily();

            if ($em->getUnitOfWork()->getEntityState($family) === UnitOfWork::STATE_MANAGED) {
                $updatedEntities[] = $entity->getFamily();
            }
        }

        return $updatedEntities;
    }
}
