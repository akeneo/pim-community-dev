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
final class FamilyQuery
{
    /**
     * @param array|null $includeCodes families will be searched only inside families corresponding to $includeCodes
     * @param array|null $excludeCodes families corresponding to $excludeCodes will be put out of the search
     */
    public function __construct(
        public ?FamilyQuerySearch $search = null,
        public ?FamilyQueryPagination $pagination = null,
        public ?array $includeCodes = null,
        public ?array $excludeCodes = null,
    ) {
        if (null !== $includeCodes) {
            Assert::allString($includeCodes);
        }

        if (null !== $excludeCodes) {
            Assert::allString($excludeCodes);
        }
    }
}
