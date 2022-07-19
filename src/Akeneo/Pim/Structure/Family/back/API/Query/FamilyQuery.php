<?php

namespace Akeneo\Pim\Structure\Family\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-immutable
 */
class FamilyQuery
{
    public function __construct(
        public ?string $search = null,
        public ?string $searchLanguage = null,
        public ?int $limit = null,
        public ?int $page = null,
        public ?array $includeCodes = null,
        public ?array $excludeCodes = null,
    ) {
    }
}
