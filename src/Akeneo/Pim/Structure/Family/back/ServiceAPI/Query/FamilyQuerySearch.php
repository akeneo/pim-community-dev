<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Family\ServiceAPI\Query;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @immutable
 */
class FamilyQuerySearch
{
    /**
     * @param string|null $value value searched inside code and label(s)
     * @param string|null $labelLocale value will be searched only in localized labels instead of all labels
     */
    public function __construct(
        public ?string $value = null,
        public ?string $labelLocale = null,
    ) {
        if (null !== $labelLocale) {
            Assert::notEmpty($labelLocale);
        }
    }
}
