<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\AttributeOptionType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Symfony\Component\Form\Event\DataEvent;

use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\FormFactoryInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductAttributeSubscriber implements EventSubscriberInterface
{
    protected $factory;

    public function __construct(FormFactoryInterface $factory = null)
    {
        $this->factory = $factory;
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(DataEvent $event)
    {
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        if ($data->getId()) {
            // get form
            $form = $event->getForm();

//             if ($data->getBackendType() === AbstractAttributeType::BACKEND_TYPE_OPTION) {
//                 $form->add(
//                     $this->factory->createNamed(
//                         'options',
//                         'collection',
//                         array(
//                             'type'         => new AttributeOptionType(),
//                             'allow_add'    => true,
//                             'allow_delete' => true,
//                             'by_reference' => false
//                         )
//                     )
//                 );
//             }
        }
    }
}
