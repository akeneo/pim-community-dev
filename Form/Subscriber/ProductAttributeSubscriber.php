<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Service\AttributeService;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormEvent;
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
     * @param AttributeService $service
     */
    public function __construct(AttributeService $service = null)
    {
        $this->service = $service;
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
     * @param DataEvent $event
     */
    public function preSetData(DataEvent $event)
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

        $attribute = $this->service->createAttributeFromFormData($data);

        $this->customizeForm($event->getForm(), $attribute);
    }

    /**
     * Customize the attribute form
     *
     * @param Form             $form
     * @param ProductAttribute $attribute
     */
    private function customizeForm($form, ProductAttribute $attribute)
    {
        foreach ($this->service->getInitialFields($attribute) as $field) {
            $form->add($this->factory->createNamed($field['name'], $field['fieldType'], $field['data'], $field['options']));
        }

        foreach ($this->service->getCustomFields($attribute) as $field) {
            $form->add($this->factory->createNamed($field['name'], $field['fieldType'], $field['data'], $field['options']));
        }

        // add options if creating an attribute with options
        if (!$attribute->getId()) {
            if ($attribute->getBackendType() === AbstractAttributeType::BACKEND_TYPE_OPTION) {
                $this->addOptionCollection($form);
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
