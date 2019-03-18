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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindExistingRecordCodesInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindExistingRecordCodes implements FindExistingRecordCodesInterface
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    public function __construct(InMemoryRecordRepository $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
    {
        $existingRecords = $this->recordRepository->getByReferenceEntityAndCodes($referenceEntityIdentifier, $recordCodes);
        $existingCodes = array_map(function (Record $record) {
            return $record->getCode();
        }, $existingRecords);

        return array_filter($recordCodes, function ($code) use ($existingCodes) {
            return in_array($code, $existingCodes);
        });
    }
}
