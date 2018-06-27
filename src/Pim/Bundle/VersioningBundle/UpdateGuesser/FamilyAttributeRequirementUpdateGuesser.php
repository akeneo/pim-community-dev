<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;

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
            $updatedEntities[] = $entity->getFamily();
        }

        return $updatedEntities;
    }
}
