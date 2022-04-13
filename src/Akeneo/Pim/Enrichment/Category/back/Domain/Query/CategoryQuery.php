<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\Domain\Query;

use Webmozart\Assert\Assert;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-immutable
 */
class CategoryQuery
{
    /**
     * POC Note: left should not be exposed but 'position'. Currently we order by on left in the API.
     * As left is not returned, it is just a detail of implementation of the ordering that cannot be used by the consumer of the API.
     * We should be able to change this order without breaking client.
     *
     * @param array<string> $categoryCodes
     * @param array<string, string>  array such as ['root' => 'ASC', 'left' => 'DESC'] // to improve
     */
    public function __construct(
        public ?\DateTimeImmutable $updatedAt = null,
        public array $categoryCodes = [],
        public ?int $page = null,
        public ?int $limit = null,
        public ?boolean $onlyRoot = null,
        public ?string $parentCategoryCode = null,
        public array $orderBy = [] // improve column naming API (root_category_id instead of root for example)
    ) {
        if ($this->page !== null && $this->limit == null) {
            throw new \InvalidArgumentException("Limit is mandatory when specifying page.");
        }

        //if ($this->orderBy !== null)
        //foreach ($this->orderBy as $field => $order) {
        //    Assert::allInArray()
        //}
        //$notAllowedKey = array_filter(
        //        array_keys($this->orderBy),
        //        fn ($key) => !in_array($key, ['root', 'position'])
        //);

    }
}
