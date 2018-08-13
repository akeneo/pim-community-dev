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

namespace Akeneo\EnrichedEntity\tests\back\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAttributesDetails implements FindAttributesDetailsInterface
{
    private $results = [];

    public function save(AbstractAttributeDetails $enrichedEntityDetails)
    {
        $this->results[(string) $enrichedEntityDetails->enrichedEntityIdentifier][] = $enrichedEntityDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        return $this->results[(string) $enrichedEntityIdentifier] ?? [];
    }
}
