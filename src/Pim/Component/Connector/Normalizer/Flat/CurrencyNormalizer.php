<?php

namespace Pim\Component\Connector\Normalizer\Flat;

use Pim\Component\Catalog\Model\CurrencyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a currency
 *
 * @author    Marie Minasyan <marie.minasyan@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /**
     * {@inheritdoc}
     */
    public function normalize($currency, $format = null, array $context = [])
    {
        return [
            'code'      => $currency->getCode(),
            'activated' => (int) $currency->isActivated(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CurrencyInterface && in_array($format, $this->supportedFormats);
    }
}
