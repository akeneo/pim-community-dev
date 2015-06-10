<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Transforms normalized values of ProductTemplate into product value objects prior to binding to the form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformProductTemplateValuesSubscriber implements EventSubscriberInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /**
     * @param NormalizerInterface   $normalizer
     * @param DenormalizerInterface $denormalizer
     */
    public function __construct(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer)
    {
        $this->normalizer   = $normalizer;
        $this->denormalizer = $denormalizer;
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

        $values = $this->denormalizer->denormalize($data->getValuesData(), 'ProductValue[]', 'json');
        $data->setValues($values);
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data || !$data instanceof ProductTemplateInterface) {
            return;
        }

        $valuesData = $this->normalizer->normalize($data->getValues(), 'json', ['entity' => 'product']);
        $data->setValuesData($valuesData);
    }
}
