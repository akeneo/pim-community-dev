<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Normalize a doctrine collection
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Pim\Bundle\TransformBundle\Normalizer\Flat\ProductNormalizer
 */
class CollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Collection && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = [];
        foreach ($object as $item) {
            $normalizedItem = $this->serializer->normalize($item, $format, $context);
            if (is_array($normalizedItem)) {
                foreach ($normalizedItem as $key => $value) {
                    if (array_key_exists($key, $result)) {
                        // TODO: this breaks product value collection denormalization into csv
                        // for prices and metric
                        $result[$key] = $result[$key] . ',' . $value;
                    } else {
                        $result = array_replace($result, $normalizedItem);
                    }
                }
            } else {
                if (is_array($result)) {
                    $result = '';
                }
                $result .= $normalizedItem . ',';
            }
        }

        if (is_array($result) && count($result) > 0) {
            return $result;
        }

        if (!isset($context['field_name'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Missing required "field_name" context value, got "%s"',
                    implode(', ', array_keys($context))
                )
            );
        }

        if (is_array($result)) {
            $result = '';
        }

        return [$context['field_name'] => rtrim($result, ',')];
    }
}
