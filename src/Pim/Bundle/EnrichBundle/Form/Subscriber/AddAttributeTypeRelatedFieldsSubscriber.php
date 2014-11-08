<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Form subscriber for AbstractAttribute
 * Allow to change field behavior like disable when editing
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeTypeRelatedFieldsSubscriber implements EventSubscriberInterface
{
    /** @var AttributeTypeRegistry */
    protected $attTypeFactory;

    /** @var FormFactoryInterface */
    protected $factory;

    /**
     * Constructor
     *
     * @param AttributeTypeRegistry $attTypeRegistry Registry
     */
    public function __construct(AttributeTypeRegistry $attTypeRegistry)
    {
        $this->attTypeRegistry = $attTypeRegistry;
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData'
        );
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

            $this->disableField($form, 'code');
        }

        $this->customizeForm($event->getForm(), $data);
    }

    /**
     * Customize the attribute form
     *
     * @param Form              $form
     * @param AbstractAttribute $attribute
     */
    protected function customizeForm(Form $form, AbstractAttribute $attribute)
    {
        $attTypeClass = $this->attTypeRegistry->get($attribute->getAttributeType());
        $fields = $attTypeClass->buildAttributeFormTypes($this->factory, $attribute);

        foreach ($fields as $field) {
            $form->add($field);
        }
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
