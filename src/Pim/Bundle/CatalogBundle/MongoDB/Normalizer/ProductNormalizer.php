<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product to store it as MongoDB JSON
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @const string */
    const FAMILY_FIELD = 'family';

    /** @const string */
    const COMPLETENESSES_FIELD = 'completenesses';

    /** @const string */
    const CREATED_FIELD = 'created';

    /** @const string */
    const UPDATED_FIELD = 'updated';

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [
            self::FAMILY_FIELD => $this->serializer->normalize($object->getFamily(), $format, $context),
            self::CREATED_FIELD => $this->serializer->normalize($object->getCreated(), $format, $context),
            self::UPDATED_FIELD => $this->serializer->normalize($object->getUpdated(), $format, $context)
        ];

        foreach ($object->getValues() as $value) {
            $data = array_merge(
                $data,
                $this->serializer->normalize($value, $format, $context)
            );
        }

        $completenesses = array();
        foreach ($object->getCompletenesses() as $completeness) {
            $completenesses = array_merge(
                $completenesses,
                $this->serializer->normalize($completeness, $format, $context)
            );
        }
        $data[self::COMPLETENESSES_FIELD] = $completenesses;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'mongodb_json' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
