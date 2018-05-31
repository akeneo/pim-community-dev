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

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\Show\ShowEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use PHPUnit\Util\Json;
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
     * Get one enriched entity
     *
     * @return JsonResponse
     */
    public function getAction(string $identifier): JsonResponse
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($identifier);
        $enrichedEntity = $this->showEnrichedEntityHandler->findByIdentifier($enrichedEntityIdentifier);
        if (null === $enrichedEntity) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            $this->enrichedEntityNormalizer->normalize($enrichedEntity, 'internal_api')
        );
    }
}
