<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Component\Catalog\Builder\LocalizableAndScopableRawValuesBuilder;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
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

    /** @var LocaleResolver */
    protected $localeResolver;

    /** @var LocalizableAndScopableRawValuesBuilder|null */
    protected $missingRawValuesBuilder;

    /**
     * @param NormalizerInterface                    $normalizer
     * @param DenormalizerInterface                  $denormalizer
     * @param LocaleResolver                         $localeResolver
     * @param LocalizableAndScopableRawValuesBuilder $missingRawValuesBuilder
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        LocaleResolver $localeResolver,
        LocalizableAndScopableRawValuesBuilder $missingRawValuesBuilder = null
    ) {
        $this->normalizer     = $normalizer;
        $this->denormalizer   = $denormalizer;
        $this->localeResolver = $localeResolver;
        $this->missingRawValuesBuilder = $missingRawValuesBuilder;
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

        $productTemplateRawValues = $data->getValuesData();
        if (null !== $this->missingRawValuesBuilder) {
            $productTemplateRawValues = $this->missingRawValuesBuilder->addMissing($productTemplateRawValues);
            $data->setValuesData($productTemplateRawValues);
        }

        $values = $this->denormalizer->denormalize(
            $data->getValuesData(),
            'ProductValue[]',
            'json',
            [
                'locale'                     => $this->localeResolver->getCurrentLocale(),
                'disable_grouping_separator' => true
            ]
        );
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

        $options = [
            'entity'                     => 'product',
            'locale'                     => $this->localeResolver->getCurrentLocale(),
            'disable_grouping_separator' => true
        ];
        $valuesData = $this->normalizer->normalize($data->getValues(), 'json', $options);
        $data->setValuesData($valuesData);
    }
}
