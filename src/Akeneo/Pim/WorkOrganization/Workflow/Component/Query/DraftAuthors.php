<?php

declare(strict_types=1);

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Query;

/**
 * Find drafts authors
 */
interface DraftAuthors
{
    public function findAuthors(?string $search, int $page = 1, int $limit = 20, array $identifiers = []): array;
}
