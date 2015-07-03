<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @var string[]
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
    public function normalize($entity, $format = null, array $context = [])
    {
        if ($entity->getData() instanceof Collection) {
            $data = [];
            foreach ($entity->getData() as $item) {
                $data[] = $this->serializer->normalize($item, $format, $context);
                sort($data);
            }
        } else {
            $data = $this->serializer->normalize($entity->getData(), $format, $context);
        }

        return [
            'locale' => $entity->getLocale(),
            'scope'  => $entity->getScope(),
            'data'   => $data
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }
}
