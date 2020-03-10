<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAttributeOptionValues
{
    /**
     * @param array $optionCodesIndexedByAttributeCodes ['color' => ['blue', 'red'], 'brand' => ['abscscd', 'weryet']]
     * @return array
     *
     * Return format:
     *  [
     *      'color' => [
     *          'blue' => ['fr_FR' => '...', 'en_US' => '...'],
     *          'red' => ['fr_FR' => '...', 'en_US' => '...'],
     *      ],
     *      ...
     * ]
     */
    public function fromOptionCodesByAttributeCode(array $optionCodesIndexedByAttributeCodes): array;
}
