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

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\FindEnrichedEntityDetailsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Get one Enriched entity by its identifier
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class GetAction
{
    /** @var FindEnrichedEntityDetailsInterface */
    private $findOneEnrichedEntityQuery;

    public function __construct(FindEnrichedEntityDetailsInterface $findOneEnrichedEntityQuery)
    {
        $this->findOneEnrichedEntityQuery = $findOneEnrichedEntityQuery;
    }

    public function __invoke(string $identifier): JsonResponse
    {
        $enrichedEntityIdentifier = $this->getEnrichedEntityIdentifierOr404($identifier);
        $enrichedEntityDetails = $this->findEnrichedEntityDetailsOr404($enrichedEntityIdentifier);

        return new JsonResponse($enrichedEntityDetails->normalize());
    }

    private function getEnrichedEntityIdentifierOr404(string $identifier): EnrichedEntityIdentifier
    {
        try {
            return EnrichedEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    private function findEnrichedEntityDetailsOr404(EnrichedEntityIdentifier $identifier): EnrichedEntityDetails
    {
        $result = ($this->findOneEnrichedEntityQuery)($identifier);
        if (null === $result) {
            throw new NotFoundHttpException();
        }

        return $result;
    }
}
