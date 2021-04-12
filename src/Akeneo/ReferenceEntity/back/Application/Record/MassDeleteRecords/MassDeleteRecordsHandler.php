<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteRecordsHandler
{
    private MassDeleteRecordsLauncherInterface $massDeleteRecordsLauncher;

    public function __construct(MassDeleteRecordsLauncherInterface $massDeleteRecordsLauncher)
    {
        $this->massDeleteRecordsLauncher = $massDeleteRecordsLauncher;
    }

    public function __invoke(MassDeleteRecordsCommand $massDeleteRecordsCommand): void
    {
        $this->massDeleteRecordsLauncher->launchForReferenceEntityAndQuery(
            ReferenceEntityIdentifier::fromString($massDeleteRecordsCommand->referenceEntityIdentifier),
            RecordQuery::createFromNormalized($massDeleteRecordsCommand->query)
        );
    }
}
