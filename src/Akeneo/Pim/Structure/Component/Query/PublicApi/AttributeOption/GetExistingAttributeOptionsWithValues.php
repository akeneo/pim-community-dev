<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetExistingAttributeOptionsWithValues
{
    /**
     * @param string $attributeCode
     * @param string[]  $optionCodes
     * @return array
     *
     * Return format:
     *  [
     *      'blue' => ['fr_FR' => '...', 'en_US' => '...'],
     *      'red' => ['fr_FR' => '...', 'en_US' => '...'],
     *      ...
     *  ]
     */
    public function fromAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes): array;
}
