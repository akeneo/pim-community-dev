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

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;

/**
 * Samir Boulil <samir.boulil@akeneo.com>
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryRecordsExists
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    public function __construct(InMemoryRecordRepository $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function withReferenceEntityAndCodes(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        array $codes
    ): array {
        $result = [];
        foreach ($codes as $code) {
            $hasRecord = true;
            try {
                $this->recordRepository->getByReferenceEntityAndCode($referenceEntityIdentifier,
                    RecordCode::fromString($code));
            } catch (RecordNotFoundException $exception) {
                $hasRecord = false;
            }

            if ($hasRecord) {
                $result[] = $code;
            }
        }

        return $result;
    }
}
