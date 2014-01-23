<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

/**
 * Attribute option update guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return in_array(
            $action,
            [UpdateGuesserInterface::ACTION_UPDATE_ENTITY, UpdateGuesserInterface::ACTION_DELETE]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(Entitymanager $em, $entity, $action)
    {
        $pendings = [];
        if ($entity instanceof AttributeOption) {
            $pendings[] = $entity->getAttribute();
        } elseif ($entity instanceof AttributeOptionValue) {
            $pendings[] = $entity->getOption()->getAttribute();
        }

        return $pendings;
    }
}
