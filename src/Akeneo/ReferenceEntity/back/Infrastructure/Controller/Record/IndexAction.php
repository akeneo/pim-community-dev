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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Records index action
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    /** @var FindRecordItemsForReferenceEntityInterface */
    private $findRecordItemsForReferenceEntityQuery;

    public function __construct(
        FindRecordItemsForReferenceEntityInterface $findRecordItemsForReferenceEntityQuery
    ) {
        $this->findRecordItemsForReferenceEntityQuery = $findRecordItemsForReferenceEntityQuery;
    }

    /**
     * Get all records belonging to an reference entity.
     */
    public function __invoke(string $referenceEntityIdentifier): JsonResponse
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifierOr404($referenceEntityIdentifier);
        $recordItems = ($this->findRecordItemsForReferenceEntityQuery)($referenceEntityIdentifier);
        $normalizedRecordItems = $this->normalizeReferenceEntityItems($recordItems);

        return new JsonResponse([
            'items' => $normalizedRecordItems,
            'total' => count($normalizedRecordItems),
        ]);
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
    private function normalizeReferenceEntityItems(array $recordItems): array
    {
        return array_map(function (RecordItem $recordItem) {
            return $recordItem->normalize();
        }, $recordItems);
    }
}
