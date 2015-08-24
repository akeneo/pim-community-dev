<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

use Doctrine\Common\EventSubscriber as DoctrineEventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events as DoctrineEvents;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

/**
 * Add parent permission when a new category is created by an import
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AddCategoryPermissionsSubscriber implements DoctrineEventSubscriber
{
    /** @var CategoryAccessManager */
    protected $accessManager;

    /**
     * Constructor
     *
     * @param CategoryAccessManager $accessManager
     */
    public function __construct(CategoryAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            DoctrineEvents::prePersist,
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     * Copy the parent's permissions to the new category
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof CategoryInterface && null === $entity->getId()) {
            $this->accessManager->setAccessLikeParent($entity, ['owner' => false], false);
        }
    }
}
