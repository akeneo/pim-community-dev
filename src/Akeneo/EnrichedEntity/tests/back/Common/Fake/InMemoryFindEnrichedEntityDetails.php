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

namespace Akeneo\EnrichedEntity\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\FindEnrichedEntityDetailsInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindEnrichedEntityDetails implements FindEnrichedEntityDetailsInterface
{
    /** @var EnrichedEntityDetails[] */
    private $results = [];

    public function save(EnrichedEntityDetails $enrichedEntityDetails)
    {
        $key = $this->getKey($enrichedEntityDetails->identifier);
        $this->results[$key] = $enrichedEntityDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        EnrichedEntityIdentifier $enrichedEntityIdentifier
    ): ?EnrichedEntityDetails {
        $key = $this->getKey($enrichedEntityIdentifier);

        return $this->results[$key] ?? null;
    }

    private function getKey(
        EnrichedEntityIdentifier $enrichedEntityIdentifier
    ): string {
        return (string)$enrichedEntityIdentifier;
    }
}
