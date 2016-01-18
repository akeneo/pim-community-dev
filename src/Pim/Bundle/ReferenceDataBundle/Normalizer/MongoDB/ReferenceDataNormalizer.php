<?php

namespace Pim\Bundle\ReferenceDataBundle\Normalizer\MongoDB;

use Pim\Bundle\TransformBundle\Normalizer\MongoDB\ProductNormalizer;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer especially used in documents normalization for MongoDB.
 * The namespace follows the TransformBundle one : Pim\Bundle\TransformBundle\Normalizer\MongoDB
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = [ProductNormalizer::FORMAT];

    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        return $data->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ReferenceDataInterface && in_array($format, $this->supportedFormats);
    }
}
