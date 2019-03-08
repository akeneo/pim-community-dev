<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\back\Infrastructure\Persistence\Sql\Record\RefreshRecords;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshAllRecords
{
    /** @var SelectRecordIdentifiersInterface */
    private $selectRecordIdentifiers;

    /** @var RefreshRecord */
    private $refreshRecord;

    public function __construct(
        SelectRecordIdentifiersInterface $selectRecordIdentifiers,
        RefreshRecord $refreshRecord
    ) {
        $this->selectRecordIdentifiers = $selectRecordIdentifiers;
        $this->refreshRecord = $refreshRecord;
    }

    public function execute(): void
    {
        $recordIdentifiers = $this->selectRecordIdentifiers->fetch();
        foreach ($recordIdentifiers as $recordIdentifier) {
            $this->refreshRecord->refresh($recordIdentifier);
        }
    }
}
