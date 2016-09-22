<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a DateTime as ISO-8601 (with the timezone)
 * See https://en.wikipedia.org/wiki/ISO_8601
 *
 * Example: 2016-06-13T12:07:58+00:00
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($date, $format = null, array $context = [])
    {
        return $date->format('c');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime && 'standard' === $format;
    }
}
