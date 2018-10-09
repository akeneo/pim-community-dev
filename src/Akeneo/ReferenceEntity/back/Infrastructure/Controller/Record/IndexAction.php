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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Record;

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\SearchRecord;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * Records index action
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    /** @var SearchRecord */
    private $searchRecord;

    public function __construct(
        SearchRecord $searchRecord
    ) {
        $this->searchRecord = $searchRecord;
    }

    /**
     * Get all records belonging to an reference entity.
     */
    public function __invoke(Request $request, string $referenceEntityIdentifier): JsonResponse
    {
        $normalizedQuery = json_decode($request->getContent(), true);
        $query = RecordQuery::createFromNormalized($normalizedQuery);
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifierOr404($referenceEntityIdentifier);

        $searchResult = ($this->searchRecord)($query);

        return new JsonResponse($searchResult->normalize());
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getReferenceEntityIdentifierOr404(string $identifier): ReferenceEntityIdentifier
    {
        try {
            return ReferenceEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @param RecordItem[] $recordItems
     *
     * @return array
     */
    private function normalizeRecodResult(array $recordResult): array
    {
        $recordResult['items'] = array_map(function (RecordItem $recordItem) {
            return $recordItem->normalize();
        }, $recordResult['items']);

        return $recordResult;
    }
}
