<?php


namespace Pim\Component\Catalog\VariantProduct\Query;

/**
 * Find the identifier of the complete or incomplete variant product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CompletenessGridFilterInterface
{

    /**
     * @param string $channel
     * @param string $locale
     * @param bool   $complete
     *
     * @return array
     */
    public function findVariantProductIdentifiers(string $channel, string $locale, bool $complete): array;
}