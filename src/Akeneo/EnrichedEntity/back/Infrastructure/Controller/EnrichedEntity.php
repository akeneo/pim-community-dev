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

namespace Akeneo\EnrichedEntity\back\Infrastructure\Controller;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\Show\ShowEnrichedEntityHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Enriched entity controller
 *
 * @author Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntity
{
    /** @var ShowEnrichedEntityHandler */
    private $showEnrichedEntityHandler;

    /** @var NormalizerInterface */
    private $enrichedEntityNormalizer;

    /**
     * @param ShowEnrichedEntityHandler $showEnrichedEntityHandler
     * @param NormalizerInterface       $enrichedEntityNormalizer
     */
    public function __construct(
        ShowEnrichedEntityHandler $showEnrichedEntityHandler,
        NormalizerInterface $enrichedEntityNormalizer
    ) {
        $this->showEnrichedEntityHandler = $showEnrichedEntityHandler;
        $this->enrichedEntityNormalizer  = $enrichedEntityNormalizer;
    }

    /**
     * Asset transformation action
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $enrichedEntities = $this->showEnrichedEntityHandler->findAll();
        $normalizedEnrichedEntities = array_map(function ($enrichedEntity) {
            return $this->enrichedEntityNormalizer->normalize($enrichedEntity, 'internal_api', []);
        }, $enrichedEntities);

        return new JsonResponse([
            'items' => $normalizedEnrichedEntities,
            'total' => count($normalizedEnrichedEntities)
        ]);
    }
}
