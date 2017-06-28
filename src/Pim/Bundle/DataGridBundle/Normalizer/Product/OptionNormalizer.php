<?php

namespace Pim\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Value\OptionValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($option, $format = null, array $context = [])
    {
        $optionData = $option->getData();

        $label = '';
        if ($optionData instanceof AttributeOptionInterface) {
            if (isset($context['data_locale'])) {
                $optionData->setLocale($context['data_locale']);
            }
            $translation = $optionData->getTranslation();

            $label = null !== $translation->getValue() ?
                $translation->getValue() :
                sprintf('[%s]', $option->getData()->getCode());
        }

        return [
            'locale' => $option->getLocale(),
            'scope'  => $option->getScope(),
            'data'   => $label
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof OptionValueInterface;
    }
}
