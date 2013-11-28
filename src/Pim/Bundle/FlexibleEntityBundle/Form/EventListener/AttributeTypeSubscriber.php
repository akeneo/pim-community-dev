<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\EventListener;

use Symfony\Component\Form\Form;
use Pim\Bundle\FlexibleEntityBundle\Form\Type\AttributeOptionType;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Aims to customized attribute form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeSubscriber implements EventSubscriberInterface
{

    /**
     * Form factory
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * Constructor
     * @param FormFactoryInterface $factory
     */
    public function __construct(FormFactoryInterface $factory = null)
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

        // only when editing
        if (is_null($data->getId()) === false) {

            $form = $event->getForm();

            // add related options
            if ($data->getBackendType() === AbstractAttributeType::BACKEND_TYPE_OPTION) {
                $this->addOptionCollection($form);
            }

            $this->disableField($form, 'code');
            $this->disableField($form, 'attributeType');
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
                array(
                    'type'            => new AttributeOptionType(),
                    'allow_add'       => true,
                    'allow_delete'    => true,
                    'by_reference'    => false,
                    'auto_initialize' => false
                )
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
