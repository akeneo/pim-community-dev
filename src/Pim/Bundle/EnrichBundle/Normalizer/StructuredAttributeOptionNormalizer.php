<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\EnrichBundle\Normalizer\AttributeOptionNormalizer as BaseAttributeOptionNormalizer;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class StructuredAttributeOptionNormalizer extends BaseAttributeOptionNormalizer
{
    /** @var array */
    protected $supportedFormat = ['json'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $optionsValues = $context['onlyActivatedLocales'] ?
            $this->ensureEmptyOptionValues($object->getOptionValues()) :
            $object->getOptionValues();

        $normalizedLabels = $this->normalizeOptionsValues($optionsValues);

        return [
            'code'   => $object->getCode(),
            'labels' => $normalizedLabels,
        ];
    }

    /**
     * Normalize and return given options values
     *
     * @param Collection $optionsValues
     *
     * @return array
     */
    protected function normalizeOptionsValues($optionsValues)
    {
        $normalizedLabels = [];
        foreach ($optionsValues as $optionsValue) {
            $normalizedLabels[$optionsValue->getLocale()] = $optionsValue->getValue();
        }

        return $normalizedLabels;
    }
}
