<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Translation update guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationsUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * Entities configured as versionable
     *
     * @var array $versionableEntities
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
    public function guessUpdates(Entitymanager $em, $entity, $action)
    {
        $pendings = array();
        if ($entity instanceof AbstractTranslation) {
            $translatedEntity = $entity->getForeignKey();
            if (in_array(get_class($translatedEntity), $this->versionableEntities)) {
                $pendings[] = $translatedEntity;
            }
        }

        return $pendings;
    }
}
