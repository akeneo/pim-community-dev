<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\Factory\ProductValueFormFactory;

/**
 * Add a relevant form for each product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProductValueFormFactory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param ProductValueFormFactory $factory
     */
    public function __construct(ProductValueFormFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    /**
     * Build and add the relevant value form for each product values
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var ProductValueInterface $value */
        $value = $event->getData();
        $form  = $event->getForm();

        if (null === $value) {
            return;
        }

        $context = ['root_form_name' => $form->getRoot()->getName()];
        $valueForm = $this->factory->buildProductValueForm($value, $context);

        $form->add($valueForm);
    }
}
