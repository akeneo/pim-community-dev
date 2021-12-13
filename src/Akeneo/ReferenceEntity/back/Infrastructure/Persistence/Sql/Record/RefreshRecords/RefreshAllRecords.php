<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshAllRecords
{
    public function __construct(
        private SelectRecordIdentifiersInterface $selectRecordIdentifiers,
        private RefreshRecord $refreshRecord
    ) {
    }

    public function execute(): void
    {
        $recordIdentifiers = $this->selectRecordIdentifiers->fetch();
        foreach ($recordIdentifiers as $recordIdentifier) {
            $this->refreshRecord->refresh($recordIdentifier);
        }
    }
}
