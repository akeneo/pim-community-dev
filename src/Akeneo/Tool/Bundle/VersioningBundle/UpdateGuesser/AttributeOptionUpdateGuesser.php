<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Doctrine\ORM\EntityManager;

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
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];

        if ($entity instanceof AttributeOptionInterface) {
            $pendings[] = $entity->getAttribute();
        } elseif ($entity instanceof AttributeOptionValueInterface) {
            $pendings[] = $entity->getOption()->getAttribute();
        }

        return $pendings;
    }
}
