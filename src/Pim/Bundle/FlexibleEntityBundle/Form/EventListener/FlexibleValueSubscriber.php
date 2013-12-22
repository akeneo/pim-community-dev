<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\EventListener;

use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Symfony\Component\Form\FormInterface;

/**
 * Add a relevant form for each flexible entity value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $registry;

    /**
     * Constructor
     *
     * @param FormFactoryInterface    $factory
     * @param AttributeTypeFactory    $attributeTypeFactory
     * @param FlexibleManagerRegistry $registry
     */
    public function __construct(
        FormFactoryInterface $factory,
        AttributeTypeFactory $attributeTypeFactory,
        FlexibleManagerRegistry $registry
    ) {
        $this->factory = $factory;
        $this->attributeTypeFactory = $attributeTypeFactory;
        $this->registry = $registry;
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
        $attributeType = $this->attributeTypeFactory->get($attributeTypeAlias, 'Pim\Bundle\FlexibleEntityBundle\Model\FlexibleInterface');
        /** @var FormInterface $valueForm */
        $valueForm = $attributeType->buildValueFormType($this->factory, $value);

        // Initialize subforms which connected to flexible entities
        $dataClass = $valueForm->getConfig()->getDataClass();
        if (is_subclass_of($dataClass, 'Pim\Bundle\FlexibleEntityBundle\Model\FlexibleInterface')) {
            $flexibleManager = $this->registry->getManager($dataClass);
            $entity = $flexibleManager->createFlexible();
            $valueForm->setData($entity);
        }

        $form->add($valueForm);
    }
}
