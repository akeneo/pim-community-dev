<?php

namespace Pim\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\Catalog\Value\OptionsValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($options, $format = null, array $context = [])
    {
        $locale = isset($context['data_locale']) ? $context['data_locale'] : null;

        $labels = [];
        foreach ($options->getData() as $option) {
            $translation = $option->getTranslation($locale);
            $labels[] = null !== $translation->getValue() ? $translation->getValue() : sprintf('[%s]', $option->getCode());
        }

        sort($labels);

        return [
            'locale' => $options->getLocale(),
            'scope'  => $options->getScope(),
            'data'   => implode(', ', $labels)
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof OptionsValueInterface;
    }
}
