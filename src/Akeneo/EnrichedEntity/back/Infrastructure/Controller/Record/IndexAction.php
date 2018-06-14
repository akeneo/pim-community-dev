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

namespace Akeneo\EnrichedEntity\back\Infrastructure\Controller\Record;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Query\FindRecordItemsForEnrichedEntityQuery;
use Akeneo\EnrichedEntity\back\Domain\Query\RecordItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Records index action
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    /** @var FindRecordItemsForEnrichedEntityQuery */
    private $findRecordItemsForEnrichedEntityQuery;

    /** @var NormalizerInterface */
    private $recordItemNormalizer;

    public function __construct(
        FindRecordItemsForEnrichedEntityQuery $findRecordItemsForEnrichedEntityQuery,
        NormalizerInterface $recordItemNormalizer
    ) {
        $this->findRecordItemsForEnrichedEntityQuery = $findRecordItemsForEnrichedEntityQuery;
        $this->recordItemNormalizer = $recordItemNormalizer;
    }

    /**
     * Get all records belonging to an enriched entity.
     */
    public function __invoke(string $enrichedEntityIdentifier): JsonResponse
    {
        $enrichedEntityIdentifier = $this->getEnrichedEntityIdentifierOr404($enrichedEntityIdentifier);
        $enrichedRecordItems = ($this->findRecordItemsForEnrichedEntityQuery)($enrichedEntityIdentifier);
        $normalizedRecordItems = $this->normalizeEnrichedEntityItems($enrichedRecordItems);

        return new JsonResponse([
            'items' => $normalizedRecordItems,
            'total' => count($normalizedRecordItems),
        ]);
    }

    private function getEnrichedEntityIdentifierOr404(string $identifier): EnrichedEntityIdentifier
    {
        try {
            return EnrichedEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @param RecordItem[] $recordItems
     */
    private function normalizeEnrichedEntityItems(array $recordItems): array
    {
        return array_map(function (RecordItem $recordItem) {
            return $this->recordItemNormalizer->normalize($recordItem, 'internal_api');
        }, $recordItems);
    }
}
