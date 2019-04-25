<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Enrichment;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindAllExistentRecordsForReferenceEntityIdentifiers as QueryInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\SqlFindAllExistentRecordsForReferenceEntityIdentifiers;

/**
 * This class is an adapter to the implementation of the same query in another Bounded context
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class FindAllExistentRecordsForReferenceEntityIdentifiers implements QueryInterface
{
    /** @var SqlFindAllExistentRecordsForReferenceEntityIdentifiers */
    private $allExistentRecordForReferenceEntityIdentifiers;

    public function __construct(SqlFindAllExistentRecordsForReferenceEntityIdentifiers $allExistentRecordForReferenceEntityIdentifiers)
    {
        $this->allExistentRecordForReferenceEntityIdentifiers = $allExistentRecordForReferenceEntityIdentifiers;
    }

    public function forReferenceEntityIdentifiersAndRecordCodes(array $referenceEntityIdentifiersToCodes): array
    {
        return $this
            ->allExistentRecordForReferenceEntityIdentifiers
            ->forReferenceEntityIdentifiersAndRecordCodes($referenceEntityIdentifiersToCodes);
    }
}
