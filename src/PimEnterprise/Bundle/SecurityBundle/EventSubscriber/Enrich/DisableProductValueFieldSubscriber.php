<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Pim\Bundle\EnrichBundle\Event\CreateProductValueFormEvent;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Disable the product value form
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DisableProductValueFieldSubscriber implements EventSubscriberInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::CREATE_VALUE_FORM => 'onCreateProductValueForm',
        ];
    }

    /**
     * Disable the product value field when user has only the read right
     *
     * @param CreateProductValueFormEvent $event
     */
    public function onCreateProductValueForm(CreateProductValueFormEvent $event)
    {
        $value = $event->getProductValue();
        $attributeGroup = $value->getAttribute()->getGroup();
        $eventContext   = $event->getContext();
        $isCreateForm   = isset($eventContext['root_form_name'])
            && $eventContext['root_form_name'] === 'pim_product_create';

        if (!$isCreateForm
            && false === $this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $attributeGroup)) {
            $formOptions = $event->getFormOptions();
            $formOptions['disabled']  = true;
            $formOptions['read_only'] = true;
            $event->updateFormOptions($formOptions);
        }
    }
}
