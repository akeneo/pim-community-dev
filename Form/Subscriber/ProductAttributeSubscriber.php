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
            $form = $event->getForm();
            foreach ($data->getActivatedProperties() as $property) {
                if ($params = $data->getFieldParams($property)) {
                    $form->add($this->factory->createNamed($property, $params['fieldType'], $params['data'], $params['options']));
                }
            }
        }
    }
}
