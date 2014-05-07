<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Event\FilterProductEvent;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use Pim\Bundle\EnrichBundle\Event\CreateProductValueFormEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Disable the product value form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DisableProductValueFieldListener implements EventSubscriberInterface
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EnrichEvents::CREATE_PRODUCT_VALUE_FORM => array('onCreateProductValueForm'),
        );
    }

    /**
     * Disable the product value field when user has only the read right
     *
     * @param CreateProductValueFormEvent $event
     */
    public function onCreateProductValueForm(CreateProductValueFormEvent $event)
    {
        $value = $event->getProductValue();
        $attributeGroup = $value->getAttribute()->getVirtualGroup();

        if (false === $this->securityContext->isGranted('GROUP_EDIT_ATTRIBUTES', $attributeGroup)) {
            $formOptions = $event->getFormOptions();
            $formOptions['disabled'] = true;
            $event->updateFormOptions($formOptions);
        }
    }
}
