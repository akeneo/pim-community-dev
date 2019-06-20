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

namespace Akeneo\ReferenceEntity\Domain\Query\Record\Connector;

use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * Find connector records by identifiers.
 * The record values will be filtered by the filters defined in the search query.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindConnectorRecordsByIdentifiersInterface
{
    /**
     * @param string[]    $identifiers
     * @param RecordQuery $recordQuery
     *
     * @return ConnectorRecord[]
     */
    public function find(array $identifiers, RecordQuery $recordQuery): array;
}
