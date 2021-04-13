<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords\MassDeleteRecordsLauncherInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use PHPUnit\Framework\Assert;

class MassDeleteRecordsLauncherSpy implements MassDeleteRecordsLauncherInterface
{
    private ?ReferenceEntityIdentifier $referenceEntityIdentifier;
    private ?RecordQuery $recordQuery;

    public function launchForReferenceEntityAndQuery(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordQuery $recordQuery
    ): void {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->recordQuery = $recordQuery;
    }

    public function hasLaunchedMassDelete(string $referenceEntityIdentifier, RecordQuery $recordQuery)
    {
        Assert::assertEquals(
            $referenceEntityIdentifier,
            (string) $this->referenceEntityIdentifier,
            sprintf(
                'Expected mass delete launcher to be launched with %s',
                $referenceEntityIdentifier
            )
        );

        Assert::assertEquals(
            $recordQuery->normalize(),
            $this->recordQuery->normalize(),
            sprintf(
                'Expected mass delete launcher to be launched with %s',
                json_encode($recordQuery)
            )
        );
    }
}
