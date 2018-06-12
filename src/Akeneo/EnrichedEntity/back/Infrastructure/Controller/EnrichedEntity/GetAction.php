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

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails\FindEnrichedEntityQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Enriched entity get action
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class GetAction
{
    /** @var FindEnrichedEntityQuery */
    private $findEnrichedEntityQuery;

    /** @var NormalizerInterface */
    private $enrichedEntityDetailsNormalizer;

    /**
     * @param FindEnrichedEntityQuery $findEnrichedEntityQuery
     * @param NormalizerInterface     $enrichedEntityDetailsNormalizer
     */
    public function __construct(
        FindEnrichedEntityQuery $findEnrichedEntityQuery,
         $enrichedEntityDetailsNormalizer
    ) {
        $this->findEnrichedEntityQuery = $findEnrichedEntityQuery;
        $this->enrichedEntityDetailsNormalizer = $enrichedEntityDetailsNormalizer;
    }

    /**
     * Get one enriched entity
     *
     * @return JsonResponse
     */
    public function __invoke(string $identifier): JsonResponse
    {
        $enrichedEntityDetails = ($this->findEnrichedEntityQuery)($identifier);

        if (null === $enrichedEntityDetails) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->normalizeEnrichedEntityDetails($enrichedEntityDetails));
    }

    /**
     * @param EnrichedEntityDetails $enrichedEntityDetails
     *
     * @return array
     */
    private function normalizeEnrichedEntityDetails(EnrichedEntityDetails $enrichedEntityDetails): array
    {
        return $this->enrichedEntityDetailsNormalizer->normalize($enrichedEntityDetails);
    }
}
