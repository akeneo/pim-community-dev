<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ProductTemplateValuesTransformer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Transforms normalized values of ProductTemplate into product value objects prior to binding to the form
 *
 * TODO: Perhaps there is a way to use the transformer directly in the form?
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformProductTemplateValuesSubscriber implements EventSubscriberInterface
{
    /** @var ProductTemplateValuesTransformer $transformer */
    protected $transformer;

    /**
     * @param ProductTemplateValuesTransformer $transformer
     */
    public function __construct(ProductTemplateValuesTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT  => 'postSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data || !$data instanceof ProductTemplateInterface) {
            return;
        }

        $data->setValues($this->transformer->transform($data->getValuesData()));
    }

    /**
     * @param FormEvent $event
     *
     * @return null
     */
    public function postSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data || !$data instanceof ProductTemplateInterface) {
            return;
        }

        $data->setValuesData($this->transformer->reverseTransform($data->getValues()));
    }
}
