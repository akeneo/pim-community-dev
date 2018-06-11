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

namespace Akeneo\EnrichedEntity\back\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList\EnrichedEntityItem;
use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList\FindEnrichedEntitiesQuery;
use Akeneo\EnrichedEntity\back\Infrastructure\Normalizer\EnrichedEntityItemNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Enriched entity index action
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    /** @var FindEnrichedEntitiesQuery */
    private $findEnrichedEntitiesQuery;

    /** @var NormalizerInterface */
    private $enrichedEntityItemNormalizer;

    /**
     * @param FindEnrichedEntitiesQuery $findEnrichedEntitiesQuery
     * @param NormalizerInterface       $enrichedEntityItemNormalizer
     */
    public function __construct(
        FindEnrichedEntitiesQuery $findEnrichedEntitiesQuery,
        NormalizerInterface $enrichedEntityItemNormalizer
    ) {
        $this->findEnrichedEntitiesQuery = $findEnrichedEntitiesQuery;
        $this->enrichedEntityItemNormalizer = $enrichedEntityItemNormalizer;
    }

    /**
     * Get all enriched entities
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $enrichedEntityItems = ($this->findEnrichedEntitiesQuery)();
        $normalizedEnrichedEntityItems = $this->normalizeEnrichedEntityItems($enrichedEntityItems);

        return new JsonResponse([
            'items' => $normalizedEnrichedEntityItems,
            'total' => count($normalizedEnrichedEntityItems),
        ]);
    }

    /**
     * @param EnrichedEntityItem[] $enrichedEntityItems
     *
     * @return array
     */
    private function normalizeEnrichedEntityItems(array $enrichedEntityItems): array
    {
        return array_map(function (EnrichedEntityItem $enrichedEntityItem) {
            return $this->enrichedEntityItemNormalizer->normalize($enrichedEntityItem, 'internal_api');
        }, $enrichedEntityItems);
    }
}
