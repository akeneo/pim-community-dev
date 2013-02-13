<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\Form\Form;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeOptionType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Symfony\Component\Form\Event\DataEvent;

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
     * @param DataEvent $event
     */
    public function preSetData(DataEvent $event)
    {
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        // only when editing
        if ($data->getId()) {
            // get form
            $form = $event->getForm();

            // Add attribute option type before editing
            if ($data->getBackendType() === AbstractAttributeType::BACKEND_TYPE_OPTION) {
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

            // Disabled fields editing
            $this->disableField($form, 'code');
            $this->disableField($form, 'attributeType');
        }
    }

    /**
     * Disable a field from its name
     * @param Form   $form Form
     * @param string $name Field name
     */
    protected function disableField(Form $form, $name)
    {
        // get form field and field properties
        $formField = $form->get($name);

        $type = $formField->getConfig()->getType();
        $options = $formField->getConfig()->getOptions();

        // set disabled and read-only
        $options['disabled'] = true;
        $options['read_only'] = true;

        // replace field in form
        $formField = $this->factory->createNamed($name, $type, null, $options);
        $form->add($formField);
    }
}
