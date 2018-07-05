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
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\RecordDetails;
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

    public function __invoke(string $enrichedEntityIdentifier, string $recordIdentifier): JsonResponse
    {
        $enrichedEntityIdentifier = $this->getEnrichedEntityIdentifierOr404($enrichedEntityIdentifier);
        $recordIdentifier = $this->getRecordIdentifierOr404($recordIdentifier);
        $recordDetails = $this->findRecordDetailsOr404($enrichedEntityIdentifier, $recordIdentifier);

        return new JsonResponse($recordDetails->normalize());
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getEnrichedEntityIdentifierOr404(string $identifier): EnrichedEntityIdentifier
    {
        try {
            return EnrichedEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getRecordIdentifierOr404(string $identifier): RecordIdentifier
    {
        try {
            return RecordIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function findRecordDetailsOr404(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordIdentifier $recordIdentifier
    ): RecordDetails {
        $result = ($this->findRecordDetailsQuery)($enrichedEntityIdentifier, $recordIdentifier);

        if (null === $result) {
            throw new NotFoundHttpException();
        }

        return $result;
    }
}
