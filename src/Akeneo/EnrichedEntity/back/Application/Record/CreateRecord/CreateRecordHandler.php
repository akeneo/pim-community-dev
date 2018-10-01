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

namespace Akeneo\ReferenceEntity\Application\Record\CreateRecord;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;

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
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($createRecordCommand->referenceEntityIdentifier);
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $code);

        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $createRecordCommand->labels,
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );

        $this->recordRepository->create($record);
    }
}
