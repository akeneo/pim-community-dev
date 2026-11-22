<?php

namespace Akeneo\Channel\Infrastructure\Component\Normalizer\Standard;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($currency, $format = null, array $context = []): \ArrayObject|array|string|int|float|bool|null
    {
        return [
            'code'    => $currency->getCode(),
            'enabled' => (bool) $currency->isActivated(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof CurrencyInterface && 'standard' === $format;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
