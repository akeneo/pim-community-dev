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
     * @param string[] $optionKeys Format: ["<attribute_code>.<option_code>", "color.red", ...]
     * @return array Format:
     *  [
     *      'color.red' => ['fr_FR' => '...', 'en_US' => '...'],
     *      'color.blue' => ['fr_FR' => '...', 'en_US' => '...'],
     *      ...
     *  ]
     */
    public function fromAttributeCodeAndOptionCodes(array $optionKeys): array;
}
