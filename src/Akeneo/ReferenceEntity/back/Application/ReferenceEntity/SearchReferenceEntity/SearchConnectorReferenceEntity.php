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

use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityItemsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityQuery;

/**
 * This service takes a reference entity search query and will return a list of connector reference entities
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchConnectorReferenceEntity
{
    /** @var FindConnectorReferenceEntityItemsInterface */
    private $findConnectorReferenceEntityItems;

    public function __construct(
        FindConnectorReferenceEntityItemsInterface $findConnectorReferenceEntityItems
    ) {
        $this->findConnectorReferenceEntityItems = $findConnectorReferenceEntityItems;
    }

    public function __invoke(ReferenceEntityQuery $query): array
    {
        $result = ($this->findConnectorReferenceEntityItems)($query);
        // @TODO - This returns empty ?
        $records = empty($result) ? [] : ($this->findConnectorReferenceEntityItems)($query);

        return $records;
    }
}
