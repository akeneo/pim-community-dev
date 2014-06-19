<?php

namespace PimEnterprise\Bundle\SecurityBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;

use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

/**
 * Subscriber to remove product value when user has no right to at least see it
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RemoveProductValueSubscriber implements EventSubscriberInterface
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
            FormEvents::POST_SET_DATA => 'removeProductValues'
        );
    }

    /**
     * Remove the product values field
     *
     * @param FormEvent $event
     */
    public function removeProductValues(FormEvent $event)
    {
        $form       = $event->getForm();
        $formValues = $form->get('values');

        foreach ($formValues as $formValue) {
            $productValue = $formValue->getData();
            $attribute = $productValue->getAttribute();
            $attributeGroup = $attribute->getGroup();
            if (false === $this->securityContext->isGranted(AttributeGroupVoter::VIEW_ATTRIBUTES, $attributeGroup)) {
                $formValueName = $formValue->getName();
                $formValues->remove($formValueName);
            }
        }
    }
}
