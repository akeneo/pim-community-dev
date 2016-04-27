<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Helper;

/**
 * Normalize doctrine result set from flat format into structured one.
 *
 * @author    Langlade Arnaud <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResultParser
{
    /**
     * Extract translations for a locale from a result set of doctrine query (array hydration)
     *
     * In :
     * [
     *     ['id' => 10, 'label' => 'group fr', 'code' => 'group_code', 'locale' => 'fr_FR'],
     *     ['id' => 10, 'label' => 'group en', 'code' => 'group_code', 'locale' => 'en_US'],
     *     ['id' => 11, 'label' => null, 'code' => 'group_other_code', 'locale' => 'fr_FR']
     * ]
     *
     * Out :
     * [
     *     10 => 'group en',
    *      11 => '[group_other_code]',
     * ]
     *
     * @param array  $flatTranslations
     * @param string $locale
     *
     * @return array
     */
    public static function parseTranslations(array $flatTranslations, $locale)
    {
        $formattedTranslations = function ($carry, array $item) {
            if (!empty($item['label'])) {
                $carry[$item['id']]['labels'][$item['locale']] = $item['label'];
            }
            $carry[$item['id']]['code'] = sprintf('[%s]', $item['code']);

            return $carry;
        };

        $extractLabel = function (array $item) use ($locale) {
            $label = isset($item['labels'][$locale]) ? $item['labels'][$locale] : $item['code'];

            return $label;
        };

        $flatTranslations = array_map($extractLabel, array_reduce($flatTranslations, $formattedTranslations, []));

        return $flatTranslations;

    }
}
