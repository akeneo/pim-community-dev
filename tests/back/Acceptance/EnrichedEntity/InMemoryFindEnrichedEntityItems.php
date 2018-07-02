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

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityItem;
use Akeneo\EnrichedEntity\Domain\Query\FindEnrichedEntityDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\FindEnrichedEntityItemsInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindEnrichedEntityItems implements FindEnrichedEntityItemsInterface
{
    /** @var EnrichedEntityItem[] */
    private $results = [];

    public function save(EnrichedEntityItem $enrichedEntityDetails)
    {
        $this->results[] = $enrichedEntityDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(): array {
        return $this->results;
    }
}
