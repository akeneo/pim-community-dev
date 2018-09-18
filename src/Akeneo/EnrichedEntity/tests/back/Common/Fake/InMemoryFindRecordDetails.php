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
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordDetails implements FindRecordDetailsInterface
{
    /** @var RecordDetails[] */
    private $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(RecordDetails $recordDetails)
    {
        $this->results[sprintf('%s____%s', $recordDetails->enrichedEntityIdentifier, $recordDetails->code)] = $recordDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $recordCode
    ): ?RecordDetails {
        return $this->results[sprintf('%s____%s', $enrichedEntityIdentifier, $recordCode)] ?? null;
    }
}
