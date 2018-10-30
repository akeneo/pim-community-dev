<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIndexerSpy implements RecordIndexerInterface
{
    /** @var array */
    private $indexedReferenceEntities = [];

    public function index(RecordIdentifier $recordIdentifier)
    {
        throw new NotImplementedException('index');
    }

    /**
     * Indexes all records belonging to the given reference entity.
     */
    public function indexByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): void
    {
        $this->indexedReferenceEntities[] = $referenceEntityIdentifier->normalize();
    }

    /**
     * Remove all records belonging to a reference entity
     */
    public function removeByReferenceEntityIdentifier(string $referenceEntityIdentifier)
    {
        throw new NotImplementedException('removeByReferenceEntityIdentifier');
    }

    /**
     * Remove a record from the index
     */
    public function removeRecordByReferenceEntityIdentifierAndCode(
        string $referenceEntityIdentifier,
        string $recordCode
    ) {
        throw new NotImplementedException('removeRecordByReferenceEntityIdentifierAndCode');
    }

    public function assertReferenceEntityNotIndexed(string $referenceEntityIdentifier)
    {
        Assert::assertNotContains($referenceEntityIdentifier, $this->indexedReferenceEntities);
    }

    public function assertReferenceEntityIndexed(string $referenceEntityIdentifier)
    {
        Assert::assertContains($referenceEntityIdentifier, $this->indexedReferenceEntities);
    }
}
