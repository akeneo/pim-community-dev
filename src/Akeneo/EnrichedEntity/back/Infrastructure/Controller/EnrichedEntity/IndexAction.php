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

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\ListEnrichedEntityHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Enriched entity index action
 *
 * @author Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    /** @var ListEnrichedEntityHandler */
    private $listEnrichedEntityHandler;

    /** @var NormalizerInterface */
    private $enrichedEntityNormalizer;

    /**
     * @param ListEnrichedEntityHandler $listEnrichedEntityHandler
     * @param NormalizerInterface       $enrichedEntityNormalizer
     */
    public function __construct(
        ListEnrichedEntityHandler $listEnrichedEntityHandler,
        NormalizerInterface $enrichedEntityNormalizer
    ) {
        $this->listEnrichedEntityHandler = $listEnrichedEntityHandler;
        $this->enrichedEntityNormalizer  = $enrichedEntityNormalizer;
    }

    /**
     * Get all enriched entities
     *
     * @return JsonResponse
     */
    public function indexAction(): JsonResponse
    {
        $enrichedEntities = ($this->listEnrichedEntityHandler)();
        $normalizedEnrichedEntities = array_map(function ($enrichedEntity) {
            return $this->enrichedEntityNormalizer->normalize($enrichedEntity, 'internal_api');
        }, $enrichedEntities);

        return new JsonResponse([
            'items' => $normalizedEnrichedEntities,
            'total' => count($normalizedEnrichedEntities)
        ]);
    }
}
