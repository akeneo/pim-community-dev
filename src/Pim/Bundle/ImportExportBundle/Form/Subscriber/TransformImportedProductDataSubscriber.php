<?php

namespace Pim\Bundle\ImportExportBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Converter\ProductValueConverter;

/**
 * Transform imported product data into a bindable data to the product form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProductValueConverter $valueConverter
     */
    protected $valueConverter;

    /**
     * Constructor
     * @param ProductValueConverter $valueConverter
     */
    public function __construct(ProductValueConverter $valueConverter)
    {
        $this->valueConverter      = $valueConverter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    /**
     * Transform the imported product data to allow binding them to the form
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        try {
            $event->setData(
                array_intersect_key($data, array_flip($this->getProductFields($event))) +
                $this->valueConverter->convert($data)
            );
        } catch (\InvalidArgumentException $e) {
            throw new InvalidItemException($e->getMessage(), $data);
        }
    }

    /**
     * Returns the name of the fields of the Product entity
     *
     * @param  FormEvent $event
     * @return type
     */
    protected function getProductFields(FormEvent $event)
    {
        $options = $event->getForm()->getConfig()->getOptions();

        return array(
            'enabled',
            $options['family_column'],
            $options['categories_column'],
            $options['groups_column']
        );
    }
}
