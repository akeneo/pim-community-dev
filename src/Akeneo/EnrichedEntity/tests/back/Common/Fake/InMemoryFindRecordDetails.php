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

use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\RecordDetails;

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
        $key = $this->getKey($recordDetails->identifier);
        $this->results[$key] = $recordDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        RecordIdentifier $recordIdentifier
    ): ?RecordDetails {
        $key = $this->getKey($recordIdentifier);

        return $this->results[$key] ?? null;
    }

    private function getKey(RecordIdentifier $recordIdentifier): string
    {
        return sprintf('%s_%s', $recordIdentifier->getEnrichedEntityIdentifier(), $recordIdentifier->getIdentifier());
    }
}
