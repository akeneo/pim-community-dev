<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Manager\AttributeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Form subscriber for AttributeInterface
 * Allow to change field behavior like disable when editing
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeTypeRelatedFieldsSubscriber implements EventSubscriberInterface
{
    /**
     * Attribute manager
     * @var AttributeManagerInterface
     */
    protected $attributeManager;

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
     * @param AttributeManagerInterface $attributeManager Attribute manager
     * @param AttributeTypeFactory      $attTypeFactory   Attribute type factory
     */
    public function __construct(
        AttributeManagerInterface $attributeManager = null,
        AttributeTypeFactory $attTypeFactory = null
    ) {
        $this->attributeManager = $attributeManager;
        $this->attTypeFactory   = $attTypeFactory;
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
        return [
            FormEvents::PRE_BIND => 'preBind',
            FormEvents::PRE_SET_DATA => 'preSetData'
        ];
    }

    /**
     * Method called before set data
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) {
            return;
        }

        if (is_null($data->getId()) === false) {

            $form = $event->getForm();

            // add related options
            if ($data->getBackendType() === AbstractAttributeType::BACKEND_TYPE_OPTION) {
                $this->addOptionCollection($form);
            }

            $this->disableField($form, 'code');
            $this->disableField($form, 'attributeType');
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

        $attribute = $this->attributeManager->createAttributeFromFormData($data);

        $this->customizeForm($event->getForm(), $attribute);
    }

    /**
     * Customize the attribute form
     *
     * @param Form               $form      AttributeInterface form
     * @param AttributeInterface $attribute AttributeInterface entity
     */
    protected function customizeForm(Form $form, AttributeInterface $attribute)
    {
        $attTypeClass = $this->attTypeFactory->get($attribute->getAttributeType());
        $fields = $attTypeClass->buildAttributeFormTypes($this->factory, $attribute);

        foreach ($fields as $field) {
            $form->add($field);
        }
    }

    /**
     * Add attribute option collection
     * @param Form $form
     */
    protected function addOptionCollection($form)
    {
        $form->add(
            $this->factory->createNamed(
                'options',
                'collection',
                null,
                [
                    'type'            => 'pim_catalog_attribute_option',
                    'allow_add'       => true,
                    'allow_delete'    => true,
                    'by_reference'    => false,
                    'auto_initialize' => false
                ]
            )
        );
    }

    /**
     * Disable a field from its name
     * @param Form   $form Form
     * @param string $name Field name
     */
    protected function disableField(Form $form, $name)
    {
        // get form field and properties
        $formField = $form->get($name);
        $type      = $formField->getConfig()->getType();
        $options   = $formField->getConfig()->getOptions();

        // replace by disabled and read-only
        $options['disabled']  = true;
        $options['read_only'] = true;
        $options['auto_initialize'] = false;
        $formField = $this->factory->createNamed($name, $type, null, $options);
        $form->add($formField);
    }
}
