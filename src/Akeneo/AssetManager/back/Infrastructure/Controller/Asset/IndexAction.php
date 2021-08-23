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

namespace Akeneo\AssetManager\Infrastructure\Controller\Asset;

use Akeneo\AssetManager\Application\Asset\SearchAsset\SearchAsset;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Assets index action
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    private SearchAsset $searchAsset;

    public function __construct(SearchAsset $searchAsset)
    {
        $this->searchAsset = $searchAsset;
    }

    /**
     * Get all assets belonging to an asset family.
     */
    public function __invoke(Request $request, string $assetFamilyIdentifier): JsonResponse
    {
        $normalizedQuery = json_decode($request->getContent(), true);

        if (null === $normalizedQuery) {
            throw new BadRequestHttpException('Invalid JSON message received');
        }

        $query = AssetQuery::createFromNormalized($normalizedQuery);
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($assetFamilyIdentifier);

        if ($this->hasDesynchronizedIdentifiers($assetFamilyIdentifier, $query)) {
            return new JsonResponse(
                'The asset family identifier provided in the route and the one given in the request body are different',
                Response::HTTP_BAD_REQUEST
            );
        }

        $searchResult = ($this->searchAsset)($query);

        return new JsonResponse($searchResult->normalize());
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getAssetFamilyIdentifierOr404(string $identifier): AssetFamilyIdentifier
    {
        try {
            return AssetFamilyIdentifier::fromString($identifier);
        } catch (\Exception $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedIdentifiers(
        AssetFamilyIdentifier $routeAssetFamilyIdentifier,
        AssetQuery $query
    ): bool {
        return (string) $routeAssetFamilyIdentifier !== $query->getFilter('asset_family')['value'];
    }
}
