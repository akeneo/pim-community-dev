<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Pim\Bundle\ProductBundle\Service\AttributeService;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\EventListener\AttributeTypeSubscriber;
use Pim\Bundle\ProductBundle\Form\Type\AttributeOptionType as ProductAttributeOptionType;

/**
 * Form subscriber for ProductAttribute
 * Allow to change field behavior like disable when editing
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeSubscriber extends AttributeTypeSubscriber
{

    /**
     * Attribute service
     * @var AttributeService
     */
    protected $service;

    /**
     * Form factory
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param AttributeService     $service
     */
    public function __construct(FormFactoryInterface $factory = null, AttributeService $service = null)
    {
        $this->factory = $factory;
        $this->service = $service;
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
     * @param DataEvent $event
     */
    public function preSetData(DataEvent $event)
    {
        parent::preSetData($event);
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        $form = $event->getForm();

        foreach ($this->service->getInitialFields($data) as $field) {
            $form->add($this->factory->createNamed($field['name'], $field['fieldType'], $field['data'], $field['options']));
        }

        // only when editing
        if ($data->getId()) {
            foreach ($this->service->getCustomFields($data) as $field) {
                $form->add($this->factory->createNamed($field['name'], $field['fieldType'], $field['data'], $field['options']));
            }
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
                    'type'         => new ProductAttributeOptionType(),
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => false
                )
            )
        );
    }
}
