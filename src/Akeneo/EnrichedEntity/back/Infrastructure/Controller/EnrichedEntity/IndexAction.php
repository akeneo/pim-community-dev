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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityItem;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\FindEnrichedEntityItemsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * List enriched entities
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    /** @var FindEnrichedEntityItemsInterface */
    private $findEnrichedEntitiesQuery;

    public function __construct(FindEnrichedEntityItemsInterface $findEnrichedEntitiesQuery)
    {
        $this->findEnrichedEntitiesQuery = $findEnrichedEntitiesQuery;
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
        return array_map(function (EnrichedEntityItem $item) {
            return $item->normalize();
        }, $enrichedEntityItems);
    }
}
