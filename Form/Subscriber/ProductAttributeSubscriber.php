<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeOptionType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Service\AttributeService;
use Pim\Bundle\ProductBundle\Manager\ProductManager;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Form subscriber for ProductAttribute
 * Allow to change field behavior like disable when editing
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeSubscriber implements EventSubscriberInterface
{

    /**
     * Product manager service
     * @var ProductManager
     */
    protected $manager;

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
     * @param ProductManager   $manager
     * @param AttributeService $service
     */
    public function __construct(ProductManager $manager = null, AttributeService $service = null)
    {
        $this->manager = $manager;
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
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        $form = $event->getForm();

        $this->customizeForm($form, $data);
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

        $form = $event->getForm();

        // Create a productattribute from the form's data
        $data = $data['pim_product_attribute_form'];
        $attribute = $this->manager->createAttributeExtended();
        $baseProperties = $this->service->getBaseProperties();

        foreach ($data as $property => $value) {
            if (array_key_exists($property, $baseProperties) && $value !== '') {
                $set = 'set' . ucfirst($property);
                if (method_exists($attribute, $set)) {
                    if ($baseProperties[$property] === 'boolean') {
                        $value = (bool) $value;
                    } elseif ($baseProperties[$property] === 'integer') {
                        $value = (int) $value;
                    }
                    $attribute->$set($value);
                }
            }
        }

        $this->customizeForm($form, $attribute);
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
                $form->add(
                    $this->factory->createNamed(
                        'options',
                        'collection',
                        null,
                        array(
                            'type'         => new AttributeOptionType(),
                            'allow_add'    => true,
                            'allow_delete' => true,
                            'by_reference' => false
                        )
                    )
                );
            }
        }
    }
}
