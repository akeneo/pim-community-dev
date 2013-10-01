<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * A normalizer to transform a category entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatCategoryNormalizer extends CategoryNormalizer
{
    /**
     * @staticvar string
     */
    const LOCALIZABLE_PATTERN = '{locale}:{value}';

    /**
     * @staticvar string
     */
    const ITEM_SEPARATOR      = ',';

    /**
     * @var array
     */
    protected $supportedFormats = array('csv');

    /**
     * Normalize the label
     *
     * @param CategoryInterface $category
     *
     * @return string
     */
    protected function normalizeLabel(CategoryInterface $category)
    {
        $pattern = self::LOCALIZABLE_PATTERN;
        $labels = $category->getTranslations()->map(
            function ($translation) use ($pattern) {
                $label = str_replace('{locale}', $translation->getLocale(), $pattern);
                $label = str_replace('{value}', $translation->getLabel(), $label);

                return $label;
            }
        )->toArray();

        return implode(self::ITEM_SEPARATOR, $labels);
    }
}
