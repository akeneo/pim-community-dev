<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

/**
 * Search on records for the given query. This query function returns only identifiers
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
interface FindRecordsForIdentifiersInterface
{
    /**
     * @return RecordItem[]
     */
    public function __invoke(array $identifiers): array;
}
