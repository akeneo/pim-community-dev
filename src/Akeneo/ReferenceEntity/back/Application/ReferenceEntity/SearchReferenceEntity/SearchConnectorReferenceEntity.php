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

namespace Akeneo\ReferenceEntity\Application\ReferenceEntity\SearchReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityQuery;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\SearchReferenceEntityResult;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityItemsInterface;


/**
 * This service takes a reference entity search query and will return a collection of reference entity items.
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SearchConnectorReferenceEntity
{
    /** @var FindConnectorReferenceEntityItemsInterface */
    private $findConnectorReferenceEntityItemsQuery;

    public function __construct(
        FindConnectorReferenceEntityItemsInterface $findConnectorReferenceEntityItemsQuery
    ) {
        $this->findConnectorReferenceEntityItemsQuery = $findConnectorReferenceEntityItemsQuery;
    }

    public function __invoke(ReferenceEntityQuery $query): SearchReferenceEntityResult
    {
        $records = ($this->findConnectorReferenceEntityItemsQuery)();

        $queryResult = new SearchReferenceEntityResult();
        $queryResult->total = $result->total;
        $queryResult->items = $records;

        return $queryResult;
    }
}
