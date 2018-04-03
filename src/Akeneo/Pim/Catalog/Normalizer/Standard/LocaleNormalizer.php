<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($locale, $format = null, array $context = [])
    {
        return [
            'code'    => $locale->getCode(),
            'enabled' => (bool) $locale->isActivated(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LocaleInterface && 'standard' === $format;
    }
}
