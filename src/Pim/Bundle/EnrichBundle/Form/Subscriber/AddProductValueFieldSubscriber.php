<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Add a relevant form for each product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueFieldSubscriber implements EventSubscriberInterface
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
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param AttributeTypeFactory $attTypeFactory
     */
    public function __construct(FormFactoryInterface $factory, AttributeTypeFactory $attTypeFactory)
    {
        $this->factory = $factory;
        $this->attributeTypeFactory = $attTypeFactory;
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
     * Build and add the relevant value form for each product values
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var ProductValueInterface $value */
        $value = $event->getData();
        $form  = $event->getForm();

        if (null === $value) {
            return;
        }

        $attributeTypeAlias = $value->getAttribute()->getAttributeType();
        $attributeType = $this->attributeTypeFactory->get($attributeTypeAlias);

        /** @var FormInterface $valueForm */
        $valueForm = $attributeType->buildValueFormType($this->factory, $value);
        $form->add($valueForm);
    }
}
