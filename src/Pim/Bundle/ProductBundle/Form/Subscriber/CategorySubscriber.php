<?php
namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\Form\FormFactoryInterface;

use Symfony\Component\Form\Event\DataEvent;

use Symfony\Component\Form\FormEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Form subscriber for Category
 * Allow to add field if working with node instead of a tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategorySubscriber implements EventSubscriberInterface
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
     * @param DataEvent $event
     */
    public function preSetData(DataEvent $event)
    {
        $data = $event->getData();

        if (null === $data || $data->getParent() === null) {
            return;
        }

        $isDynamic = $data->isDynamic() ? 1 : 0;
        $form = $event->getForm();
        // TODO New in version 2.2: The ability to pass a string into FormInterface::add was added in Symfony 2.2.
        $form->add(
            $this->factory->createNamed('dynamic', 'hidden', $isDynamic, array('required' => false))
        );
    }
}
