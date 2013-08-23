<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\ProductBundle\Model\CategoryInterface;

/**
 * A normalizer to transform a category entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatCategoryNormalizer extends CategoryNormalizer
{
    const LOCALIZABLE_PATTERN = '{locale}:{value}';
    const ITEM_SEPARATOR      = ',';

    /**
     * @var array()
     */
    protected $supportedFormats = array('csv');

    /**
     * Normalize the title
     *
     * @param CategoryInterface $category
     *
     * @return void
     */
    protected function normalizeTitle(CategoryInterface $category)
    {
        $pattern = self::LOCALIZABLE_PATTERN;
        $titles = $category->getTranslations()->map(
            function ($translation) use ($pattern) {
                $title = str_replace('{locale}', $translation->getLocale(), $pattern);
                $title = str_replace('{value}', $translation->getTitle(), $title);

                return $title;
            }
        )->toArray();

        return implode(self::ITEM_SEPARATOR, $titles);
    }
}
