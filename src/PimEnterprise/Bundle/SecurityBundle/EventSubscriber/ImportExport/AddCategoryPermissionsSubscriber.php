<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\ImportExport;

use Akeneo\Component\StorageUtils\StorageEvents;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Add parent permission when a new category is created by an import
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class AddCategoryPermissionsSubscriber implements EventSubscriberInterface
{
    /** @var CategoryAccessManager */
    protected $accessManager;

    /** @var array */
    protected $newCategoryCodes;

    /** @var string */
    protected $categoryClass;

    /** @var boolean */
    protected $withOwnerPermission;

    /**
     * Constructor
     *
     * @param CategoryAccessManager $accessManager
     * @param string                $categoryClass
     * @param boolean               $withOwnerPermission
     */
    public function __construct(CategoryAccessManager $accessManager, $categoryClass, $withOwnerPermission)
    {
        $this->accessManager = $accessManager;
        $this->newCategoryCodes = [];
        $this->categoryClass = $categoryClass;
        $this->withOwnerPermission = $withOwnerPermission;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE_ALL => 'storeNewCategoryCodes',
            StorageEvents::POST_SAVE_ALL => 'copyParentPermissions'
        ];
    }

    /**
     * Store locally the new category codes
     *
     * @param GenericEvent $event
     */
    public function storeNewCategoryCodes(GenericEvent $event)
    {
        $categories = $event->getSubject();
        foreach ($categories as $category) {
            if ($category instanceof $this->categoryClass && null === $category->getId()) {
                $this->newCategoryCodes[] = $category->getCode();
            }
        }
    }

    /**
     * Copy the parent's permissions to the new category
     *
     * @param GenericEvent $event
     */
    public function copyParentPermissions(GenericEvent $event)
    {
        $categories = $event->getSubject();
        foreach ($categories as $category) {
            if ($category instanceof $this->categoryClass && in_array($category->getCode(), $this->newCategoryCodes)) {
                $this->accessManager->setAccessLikeParent($category, ['owner' => $this->withOwnerPermission]);
                unset($this->newCategoryCodes[$category->getCode()]);
            }
        }
    }
}
