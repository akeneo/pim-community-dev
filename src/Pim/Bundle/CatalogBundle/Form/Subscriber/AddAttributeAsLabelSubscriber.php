<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;

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
     * Constructor
     *
     * @param FormFactoryInterface $factory
     */
    public function __construct(FormFactoryInterface $factory = null)
    {
        $this->factory = $factory;
    }

    /**
     * @return multitype:string
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if ($data && $data->getId()) {
            $form->add(
                $this->factory->createNamed(
                    'attributeAsLabel',
                    'entity',
                    $data->getAttributeAsLabel(),
                    array(
                        'required'        => false,
                        'empty_value'     => 'Id',
                        'label'           => 'Attribute used as label',
                        'class'           => 'Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface',
                        'choices'         => $data->getAttributeAsLabelChoices(),
                        'auto_initialize' => false,
                        'select2'         => true
                    )
                )
            );
        }
    }
}
