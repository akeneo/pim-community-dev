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
    /** @staticvar string */
    const FIELD_FAMILY = 'family';

    /** @staticvar string */
    const FIELD_GROUPS = 'groups';

    /** @staticvar string */
    const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    const FIELD_ENABLED = 'enabled';

    /** @staticvar string */
    const FIELD_ASSOCIATIONS = 'associations';

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

        $data = [];

        $data[self::FIELD_FAMILY]   = $product->getFamily() ? $product->getFamily()->getCode() : null;
        $data[self::FIELD_GROUPS]   = $product->getGroupCodes() ? explode(',', $product->getGroupCodes()) : null;
        $data[self::FIELD_CATEGORY] = $product->getCategoryCodes() ? explode(',', $product->getCategoryCodes()) : null;
        $data[self::FIELD_ENABLED]  = (int) $product->isEnabled();

        $data[self::FIELD_ASSOCIATIONS] = $this->normalizeAssociations($product->getAssociations());

        $data = $data + $this->normalizeValues($product->getValues(), $format, $context);

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
     * Normalize the values of the product
     *
     * @param ArrayCollection $values
     * @param string          $format
     * @param array           $context
     *
     * @return ArrayCollection
     */
    protected function normalizeValues($values, $format, array $context = [])
    {
        $locales  = isset($context['locales'])  ? $context['locales']  : [];
        $channels = isset($context['channels']) ? $context['channels'] : [];
        $values   = $this->filterValues($values, $channels, $locales);

        $data = [];

        foreach ($values as $value) {
            $data[$value->getAttribute()->getCode()][] = $this->serializer->normalize($value, $format, $context);
        }

        return $data;
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

    /**
     * Normalize the associations of the product
     *
     * @param Association[] $associations
     *
     * @return array
     */
    protected function normalizeAssociations($associations = [])
    {
        $data = [];

        foreach ($associations as $association) {
            $code = $association->getAssociationType()->getCode();

            foreach ($association->getGroups() as $group) {
                $data[$code]['groups'][] = $group->getCode();
            }

            foreach ($association->getProducts() as $product) {
                $data[$code]['products'][] = $product->getReference();
            }
        }

        return $data;
    }
}
