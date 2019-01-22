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

namespace Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * Handler to delete all records belonging to a reference entity
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAllReferenceEntityRecordsHandler
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function __invoke(DeleteAllReferenceEntityRecordsCommand $deleteAllRecordsCommand): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($deleteAllRecordsCommand->getReferenceEntityIdentifier());

        $this->recordRepository->deleteByReferenceEntity($referenceEntityIdentifier);
    }
}
