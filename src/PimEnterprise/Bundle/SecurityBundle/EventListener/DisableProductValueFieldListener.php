<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use Pim\Bundle\EnrichBundle\Event\CreateProductValueFormEvent;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

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
        $attributeGroup = $value->getAttribute()->getGroup();
        $eventContext   = $event->getContext();
        $isCreateForm   = isset($eventContext['root_form_name'])
            && $eventContext['root_form_name'] === 'pim_product_create';

        if (!$isCreateForm
            && false === $this->securityContext->isGranted(AttributeGroupVoter::EDIT_ATTRIBUTES, $attributeGroup)) {
            $formOptions = $event->getFormOptions();
            $formOptions['disabled']  = true;
            $formOptions['read_only'] = true;
            $event->updateFormOptions($formOptions);
        }
    }
}
