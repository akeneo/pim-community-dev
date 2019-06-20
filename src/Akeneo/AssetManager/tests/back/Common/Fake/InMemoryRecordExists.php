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
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;

/**
 * Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryRecordExists implements RecordExistsInterface
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    public function __construct(InMemoryRecordRepository $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function withIdentifier(RecordIdentifier $recordIdentifier): bool
    {
        return $this->recordRepository->hasRecord($recordIdentifier);
    }

    public function withReferenceEntityAndCode(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $code): bool
    {
        $hasRecord = true;
        try {
            $this->recordRepository->getByReferenceEntityAndCode($referenceEntityIdentifier, $code);
        } catch (RecordNotFoundException $exception) {
            $hasRecord = false;
        }

        return $hasRecord;
    }
}
