<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Oro\Bundle\FlexibleEntityBundle\Form\EventListener\AttributeTypeSubscriber;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Manager\AttributeTypeManager;

/**
 * Form subscriber for ProductAttribute
 * Allow to change field behavior like disable when editing
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeTypeRelatedFieldsSubscriber extends AttributeTypeSubscriber
{
    /**
     * Attribute type manager
     * @var AttributeTypeManager
     */
    protected $attTypeManager;

    /**
     * Attribute type factory
     * @var AttributeTypeFactory
     */
    protected $attTypeFactory;

    /**
     * Form factory
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param AttributeTypeManager $attTypeManager Attribute type manager
     * @param AttributeTypeFactory $attTypeFactory Attribute type factory
     */
    public function __construct(
        AttributeTypeManager $attTypeManager = null,
        AttributeTypeFactory $attTypeFactory = null
    ) {
        $this->attTypeManager = $attTypeManager;
        $this->attTypeFactory = $attTypeFactory;
    }

    /**
     * Set form factory
     *
     * @param FormFactoryInterface $factory
     */
    public function setFactory(FormFactoryInterface $factory = null)
    {
        $this->factory = $factory;
    }

    /**
     * List of subscribed events
     * @return multitype:string
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_BIND => 'preBind',
            FormEvents::PRE_SET_DATA => 'preSetData'
        );
    }

    /**
     * Method called before set data
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        parent::preSetData($event);
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        $this->customizeForm($event->getForm(), $data);
    }

    /**
     * Method called before binding data
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        $attribute = $this->attTypeManager->createAttributeFromFormData($data);

        $this->customizeForm($event->getForm(), $attribute);
    }

    /**
     * Customize the attribute form
     *
     * @param Form             $form      ProductAttribute form
     * @param ProductAttribute $attribute ProductAttribute entity
     */
    protected function customizeForm($form, ProductAttribute $attribute)
    {
        $attTypeClass = $this->attTypeFactory->get($attribute->getAttributeType());
        $fields = $attTypeClass->buildAttributeFormTypes($this->factory, $attribute);

        foreach ($fields as $field) {
            $form->add($field);
        }
    }
}
