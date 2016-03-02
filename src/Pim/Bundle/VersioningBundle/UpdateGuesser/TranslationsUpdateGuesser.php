<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Component\Localization\Model\TranslationInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

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
        if ($entity instanceof TranslationInterface) {
            $translatedEntity = $entity->getForeignKey();
            if ($translatedEntity instanceof VersionableInterface ||
                in_array(ClassUtils::getClass($translatedEntity), $this->versionableEntities)) {
                $pendings[] = $translatedEntity;
            }
        }

        return $pendings;
    }
}
