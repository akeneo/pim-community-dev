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

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\SearchRecord;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Records index action
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    private SearchRecord $searchRecord;

    public function __construct(SearchRecord $searchRecord)
    {
        $this->searchRecord = $searchRecord;
    }

    /**
     * Get all records belonging to a reference entity.
     */
    public function __invoke(Request $request, string $referenceEntityIdentifier): JsonResponse
    {
        $normalizedQuery = json_decode($request->getContent(), true);

        if (null === $normalizedQuery) {
            throw new BadRequestHttpException('Invalid JSON message received');
        }

        $query = RecordQuery::createFromNormalized($normalizedQuery);
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifierOr404($referenceEntityIdentifier);

        if ($this->hasDesynchronizedIdentifiers($referenceEntityIdentifier, $query)) {
            return new JsonResponse(
                'The reference entity identifier provided in the route and the one given in the request body are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $searchResult = ($this->searchRecord)($query);

        return new JsonResponse($searchResult->normalize());
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getReferenceEntityIdentifierOr404(string $identifier): ReferenceEntityIdentifier
    {
        try {
            return ReferenceEntityIdentifier::fromString($identifier);
        } catch (\Exception $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifiers(
        ReferenceEntityIdentifier $routeReferenceEntityIdentifier,
        RecordQuery $query
    ): bool {
        return (string) $routeReferenceEntityIdentifier !== $query->getFilter('reference_entity')['value'];
    }
}
