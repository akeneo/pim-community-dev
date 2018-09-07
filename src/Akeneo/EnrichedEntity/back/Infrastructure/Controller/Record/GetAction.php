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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Record get action.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class GetAction
{
    /** @var FindRecordDetailsInterface */
    private $findRecordDetailsQuery;

    public function __construct(FindRecordDetailsInterface $findRecordDetailsQuery)
    {
        $this->findRecordDetailsQuery = $findRecordDetailsQuery;
    }

    public function __invoke(string $enrichedEntityIdentifier, string $recordCode): JsonResponse
    {
        $recordCode = $this->getRecordCodeOr404($recordCode);
        $enrichedEntityIdentifier = $this->getEnrichedEntityIdentifierOr404($enrichedEntityIdentifier);
        $recordDetails = $this->findRecordDetailsOr404($enrichedEntityIdentifier, $recordCode);

        return new JsonResponse($recordDetails->normalize());
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getRecordCodeOr404(string $recordCode): RecordCode
    {
        try {
            return RecordCode::fromString($recordCode);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getEnrichedEntityIdentifierOr404(string $enrichedEntityIdentifier): EnrichedEntityIdentifier
    {
        try {
            return EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function findRecordDetailsOr404(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $recordCode
    ): RecordDetails {
        $result = ($this->findRecordDetailsQuery)($enrichedEntityIdentifier, $recordCode);

        if (null === $result) {
            throw new NotFoundHttpException();
        }

        return $result;
    }
}
