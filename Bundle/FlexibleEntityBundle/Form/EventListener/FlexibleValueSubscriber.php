<?php

namespace Oro\Bundle\FlexibleEntityBundle\Form\EventListener;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Symfony\Component\Form\FormInterface;

/**
 * Add a relevant form for each flexible entity value
 */
class FlexibleValueSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var AttributeTypeFactory
     */
    protected $attributeTypeFactory;

    /**
     * @var FlexibleManagerRegistry
     */
    protected $flexibleManagerRegistry;

    /**
     * Constructor
     *
     * @param FormFactoryInterface    $factory
     * @param AttributeTypeFactory    $attributeTypeFactory
     * @param FlexibleManagerRegistry $flexibleManagerRegistry
     */
    public function __construct(
        FormFactoryInterface $factory,
        AttributeTypeFactory $attributeTypeFactory,
        FlexibleManagerRegistry $flexibleManagerRegistry
    ) {
        $this->factory = $factory;
        $this->attributeTypeFactory = $attributeTypeFactory;
        $this->flexibleManagerRegistry = $flexibleManagerRegistry;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    /**
     * Build and add the relevant value form for each flexible entity values
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var FlexibleValueInterface $value */
        $value = $event->getData();
        $form  = $event->getForm();

        // skip form creation with no data
        if (null === $value) {
            return;
        }

        $attributeTypeAlias = $value->getAttribute()->getAttributeType();
        $attributeType = $this->attributeTypeFactory->get($attributeTypeAlias);
        /** @var FormInterface $valueForm */
        $valueForm = $attributeType->buildValueFormType($this->factory, $value);

        // Initialize subforms which connected to flexible entities
        $dataClass = $valueForm->getConfig()->getDataClass();
        if (is_subclass_of($dataClass, 'Oro\Bundle\FlexibleEntityBundle\Model\FlexibleInterface')) {
            $flexibleManager = $this->flexibleManagerRegistry->getManager($dataClass);
            $entity = $flexibleManager->createFlexible();
            $valueForm->setData($entity);
        }

        $form->add($valueForm);
    }
}
