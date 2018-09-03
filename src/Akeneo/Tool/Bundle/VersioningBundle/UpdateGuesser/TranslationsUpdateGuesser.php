<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

/**
 * Translation update guesser
 *
 * Add UpdateGuesserInterface::ACTION_DELETE in support action, since we introduce TranslatableUpdater in
 * Updaters, doctrine doesn't consider a label transaltion remove as an `update_entity` action on the parent entity,
 * we have to add `delete` action, but doctrine try to delete an already deleted entity, so we have to check the status
 * of entity in guessUpdates method.
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
        return in_array($action, [UpdateGuesserInterface::ACTION_UPDATE_ENTITY, UpdateGuesserInterface::ACTION_DELETE]);
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];
        if ($entity instanceof TranslationInterface) {
            $translatedEntity = $entity->getForeignKey();
            $state = $em->getUnitOfWork()->getEntityState($translatedEntity);

            if (UnitOfWork::STATE_REMOVED === $state) {
                return [];
            }

            if ($translatedEntity instanceof VersionableInterface ||
                in_array(ClassUtils::getClass($translatedEntity), $this->versionableEntities)) {
                $pendings[] = $translatedEntity;
            }
        }

        return $pendings;
    }
}
