<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @var string[] $supportedFormats
     */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $locales  = isset($context['locales']) ? $context['locales'] : [];
        $channels = isset($context['channels']) ? $context['channels'] : [];
        $values = $this->filterValues($product->getValues(), $channels, $locales);

        $data = [];

        foreach ($values as $value) {
            $data[$value->getAttribute()->getCode()][] = $this->serializer->normalize($value, $format, $context);
        }

        if (isset($context['resource'])) {
            $data['resource'] = $context['resource'];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns a subset of values that match the channel and locale requirements
     *
     * @param ArrayCollection $values
     * @param string[]        $channels
     * @param string[]        $locales
     *
     * @return ArrayCollection
     */
    protected function filterValues($values, array $channels = [], array $locales = [])
    {
        $values = $values->filter(
            function ($value) use ($channels) {
                return (!$value->getAttribute()->isScopable() || in_array($value->getScope(), $channels));
            }
        );

        $values = $values->filter(
            function ($value) use ($locales) {
                return (!$value->getAttribute()->isLocalizable() || in_array($value->getLocale(), $locales));
            }
        );

        return $values;
    }
}
