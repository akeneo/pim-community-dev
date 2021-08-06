<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use PHPUnit\Framework\Assert;

/**
 * Record indexer spy used for tests to check if it has been called with the right parameters.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIndexerSpy implements RecordIndexerInterface
{
    private array $indexedReferenceEntities = [];
    private array $indexedRecords = [];
    private bool $isIndexRefreshed = false;

    /**
     * Indexes all records belonging to the given reference entity.
     */
    public function indexByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): void
    {
        $this->indexedReferenceEntities[] = $referenceEntityIdentifier->normalize();
    }

    public function refresh(): void
    {
        $this->isIndexRefreshed = true;
    }

    public function assertReferenceEntityNotIndexed(string $referenceEntityIdentifier)
    {
        Assert::assertNotContains($referenceEntityIdentifier, $this->indexedReferenceEntities);
    }

    public function assertReferenceEntityIndexed(string $referenceEntityIdentifier)
    {
        Assert::assertContains($referenceEntityIdentifier, $this->indexedReferenceEntities);
    }

    public function assertRecordIndexed(string $recordIdentifier)
    {
        Assert::assertContains($recordIdentifier, $this->indexedRecords);
    }

    public function assertIndexRefreshed()
    {
        Assert::assertTrue($this->isIndexRefreshed, 'Index should be refreshed');
    }

    public function index(RecordIdentifier $recordIdentifier)
    {
    }

    public function indexByRecordIdentifiers(array $recordIdentifiers): void
    {
        $this->indexedRecords[] = $recordIdentifiers;
    }

    /**
     * Remove all records belonging to a reference entity
     */
    public function removeByReferenceEntityIdentifier(string $referenceEntityIdentifier)
    {
    }

    /**
     * Remove a record from the index
     */
    public function removeRecordByReferenceEntityIdentifierAndCode(
        string $referenceEntityIdentifier,
        string $recordCode
    ) {
    }
}
