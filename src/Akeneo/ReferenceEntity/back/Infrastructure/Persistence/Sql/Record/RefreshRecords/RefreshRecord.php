<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshRecord
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function refresh(RecordIdentifier $recordIdentifier): void
    {
        $record = $this->recordRepository->getByIdentifier($recordIdentifier);
        $this->recordRepository->update($record);
    }
}
