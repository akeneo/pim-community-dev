<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Structured\AttributeNormalizer as BaseNormalizer;

/**
 * A normalizer to transform an AttributeInterface entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer extends BaseNormalizer
{
    const DATE_FORMAT = 'Y-m-d';

    /** @var string[] */
    protected $supportedFormats = ['csv'];

    /**
     * {@inheritdoc}
     */
    protected function normalizeAvailableLocales(AttributeInterface $attribute)
    {
        $availableLocales = $attribute->getLocaleSpecificCodes();

        if (empty($availableLocales)) {
            return null;
        }

        return implode(self::ITEM_SEPARATOR, $availableLocales);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeOptions(AttributeInterface $attribute)
    {
        $options = $attribute->getOptions();

        if ($options->isEmpty()) {
            $options = '';
        } else {
            $data = [];
            foreach ($options as $option) {
                $item = [];
                foreach ($option->getOptionValues() as $value) {
                    /* @var ProductValueInterface $value */
                    $label = str_replace('{locale}', $value->getLocale(), self::LOCALIZABLE_PATTERN);
                    $label = str_replace('{value}', $value->getValue(), $label);
                    $item[] = $label;
                }
                $data[] = 'Code:'.$option->getCode().self::ITEM_SEPARATOR.implode(self::ITEM_SEPARATOR, $item);
            }
            $options = implode(self::GROUP_SEPARATOR, $data);
        }

        return $options;
    }
}
