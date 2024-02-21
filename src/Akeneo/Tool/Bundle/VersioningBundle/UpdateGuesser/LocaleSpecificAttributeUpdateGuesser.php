<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

/**
 * This class will guess if there are changes into the 'availableLocales' attribute field.
 * If guessed, it will version the 'availableLocales' and the 'locale_specific' fields.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleSpecificAttributeUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return $action === UpdateGuesserInterface::ACTION_UPDATE_COLLECTION;
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];
        if ($entity instanceof PersistentCollection
            && $entity->getOwner() instanceof AttributeInterface
            && ($entity->getMapping()['fieldName'] === 'availableLocales')) {
            $pendings[] = $entity->getOwner();
        }

        return $pendings;
    }
}
