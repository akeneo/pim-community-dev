<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceApi\JobInstance;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @immutable
 */
final class JobInstanceQueryPagination
{
    /**
     * @param int|null $limit number of job instances per page
     */
    public function __construct(
        public ?int $page = null,
        public ?int $limit = null,
    ) {
        if (null !== $page) {
            Assert::greaterThan($page, 0);
        }

        if (null !== $limit) {
            Assert::greaterThan($limit, 0);
        }
    }
}
