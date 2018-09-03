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

namespace Akeneo\EnrichedEntity\Application\Record\CreateRecord;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateRecordHandler
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function __invoke(CreateRecordCommand $createRecordCommand): void
    {
        $code = RecordCode::fromString($createRecordCommand->code);
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($createRecordCommand->enrichedEntityIdentifier);
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $code);

        $record = Record::create($identifier, $enrichedEntityIdentifier, $code, $createRecordCommand->labels, ValueCollection::fromValues([]));

        $this->recordRepository->create($record);
    }
}
