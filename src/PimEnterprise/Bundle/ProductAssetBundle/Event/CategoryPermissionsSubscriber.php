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

use PimEnterprise\Bundle\EnrichBundle\Form\Subscriber\CategoryPermissionsSubscriber as
    BaseCategoryPermissionsSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;

/**
 * Subscriber to manage permissions on categories
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class CategoryPermissionsSubscriber extends BaseCategoryPermissionsSubscriber
{
    /** @var array store the previous roles to be able to do a diff of added/removed */
    protected $previousRoles = ['view' => [], 'edit' => []];

    /**
     * Add the permissions subform to the form
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $event->getForm()->add('permissions', 'pimee_product_asset_category_permissions');
    }

    /**
     * Indicates whether the permissions should be added to the form
     *
     * @param FormEvent $event
     *
     * @return bool
     */
    protected function isApplicable(FormEvent $event)
    {
        return null !== $event->getData()
            && null !== $event->getData()->getId()
            && $this->securityFacade->isGranted('pimee_product_asset_category_edit_permissions');
    }
}
