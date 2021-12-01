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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityItemsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityItem;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * List reference entities
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    public function __construct(private FindReferenceEntityItemsInterface $findReferenceEntitiesQuery)
    {
    }

    /**
     * Get all reference entities
     */
    public function __invoke(): JsonResponse
    {
        $referenceEntityItems = $this->findReferenceEntitiesQuery->find();
        $normalizedReferenceEntityItems = $this->normalizeReferenceEntityItems($referenceEntityItems);

        return new JsonResponse([
            'items' => $normalizedReferenceEntityItems,
            'total' => count($normalizedReferenceEntityItems),
        ]);
    }

    /**
     * @param ReferenceEntityItem[] $referenceEntityItems
     */
    private function normalizeReferenceEntityItems(array $referenceEntityItems): array
    {
        return array_map(static fn (ReferenceEntityItem $item) => $item->normalize(), $referenceEntityItems);
    }
}
