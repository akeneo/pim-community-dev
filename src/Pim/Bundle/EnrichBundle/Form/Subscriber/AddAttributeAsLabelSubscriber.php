<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Add useable attributes as labels
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeAsLabelSubscriber implements EventSubscriberInterface
{
    /**
     * Form factory
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param string               $attributeClass
     * @param FormFactoryInterface $factory
     */
    public function __construct($attributeClass, FormFactoryInterface $factory)
    {
        $this->attributeClass = $attributeClass;
        $this->factory        = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'addAttributeAsLabelField');
    }

    /**
     * @param FormEvent $event
     */
    public function addAttributeAsLabelField(FormEvent $event)
    {
        $data = $event->getData();

        if ($data instanceof Family && $data->getId()) {
            $form = $event->getForm();
            $form->add(
                $this->factory->createNamed(
                    'attributeAsLabel',
                    'entity',
                    $data->getAttributeAsLabel(),
                    array(
                        'required'        => false,
                        'empty_value'     => $data->getEmptyAttributeAsLabelLabel(),
                        'label'           => 'Attribute used as label',
                        'class'           => $this->attributeClass,
                        'choices'         => $data->getAttributeAsLabelChoices(),
                        'auto_initialize' => false,
                        'select2'         => true
                    )
                )
            );
        }
    }
}
