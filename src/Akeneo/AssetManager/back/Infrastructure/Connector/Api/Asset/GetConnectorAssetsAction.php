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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Asset;

use Akeneo\AssetManager\Application\Asset\SearchAsset\SearchConnectorAsset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\Limit;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\Hal\AddHalDownloadLinkToAssetImages;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\SearchFiltersValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorAssetsAction
{
    /** @var AssetFamilyExistsInterface */
    private $assetFamilyExists;

    /** @var Limit */
    private $limit;

    /** @var SearchConnectorAsset */
    private $searchConnectorAsset;

    /** @var PaginatorInterface */
    private $halPaginator;

    /** @var AddHalDownloadLinkToAssetImages */
    private $addHalLinksToImageValues;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SearchFiltersValidator */
    private $searchFiltersValidator;

    public function __construct(
        AssetFamilyExistsInterface $assetFamilyExists,
        SearchConnectorAsset $searchConnectorAsset,
        PaginatorInterface $halPaginator,
        AddHalDownloadLinkToAssetImages $addHalLinksToImageValues,
        int $limit,
        ValidatorInterface $validator,
        SearchFiltersValidator $searchFiltersValidator
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->searchConnectorAsset = $searchConnectorAsset;
        $this->limit = new Limit($limit);
        $this->halPaginator = $halPaginator;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
        $this->validator = $validator;
        $this->searchFiltersValidator = $searchFiltersValidator;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request, string $assetFamilyIdentifier): JsonResponse
    {
        $searchFilters = $this->getSearchFiltersFromRequest($request);
        $searchFiltersErrors = !empty($searchFilters) ? $this->searchFiltersValidator->validate($searchFilters) : [];

        if (!empty($searchFiltersErrors)) {
            return new JsonResponse([
                'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The search filters have an invalid format.',
                'errors'  => JsonSchemaErrorsFormatter::format($searchFiltersErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $searchAfter = $request->get('search_after', null);
            $searchAfterCode = null !== $searchAfter ? AssetCode::fromString($searchAfter) : null;
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
            $channelReferenceValuesFilter = ChannelReference::createfromNormalized($request->get('channel', null));
            $localeIdentifiersValuesFilter = $this->getLocaleIdentifiersValuesFilterFromRequest($request);
            $assetQuery = AssetQuery::createPaginatedQueryUsingSearchAfter(
                $assetFamilyIdentifier,
                $channelReferenceValuesFilter,
                $localeIdentifiersValuesFilter,
                $this->limit->intValue(),
                $searchAfterCode,
                $this->formatSearchFilters($searchFilters)
            );
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $violations = $this->validator->validate($assetQuery);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'Invalid query parameters');
        }

        if (false === $this->assetFamilyExists->withIdentifier($assetFamilyIdentifier)) {
            throw new NotFoundHttpException(sprintf('Asset family "%s" does not exist.', $assetFamilyIdentifier));
        }

        /** @todo pull-up master: this is a backport of PIM-9702, ignore those changes. */
        $assets = ($this->searchConnectorAsset)($assetQuery);
        $assets = array_map(function (ConnectorAsset $asset) {
            return $asset->normalize();
        }, $assets);

        $assets = ($this->addHalLinksToImageValues)($assetFamilyIdentifier, $assets);
        $paginatedAssets = $this->paginateAssets(
            $assets,
            $request,
            $assetFamilyIdentifier,
            method_exists($this->searchConnectorAsset, 'getLastSortValue')
                ? $this->searchConnectorAsset->getLastSortValue()
                : [$assets[array_key_last($assets)]['code']] ?? null
        );

        return new JsonResponse($paginatedAssets);
    }

    private function paginateAssets(
        array $assets,
        Request $request,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ?array $lastSortValue
    ): array {
        $lastAssetCode = $lastSortValue[0] ?? null;

        $paginationParameters = [
            'list_route_name'     => 'akeneo_asset_manager_assets_rest_connector_get',
            'item_route_name'     => 'akeneo_asset_manager_asset_rest_connector_get',
            'search_after'        => [
                'self' => $request->get('search_after', null),
                'next' => $lastAssetCode
            ],
            'limit'               => $this->limit->intValue(),
            'item_identifier_key' => 'code',
            'uri_parameters'      => [
                'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
            ],
            'query_parameters'    => [
                'channel' => $request->get('channel', null),
                'locales' => $request->get('locales', null),
                'search'  => $request->get('search', null),
            ],
        ];

        return $this->halPaginator->paginate($assets, $paginationParameters, count($assets));
    }

    private function getLocaleIdentifiersValuesFilterFromRequest(Request $request): LocaleIdentifierCollection
    {
        $locales = $request->get('locales', '');
        $locales = '' === $locales ? [] : explode(',', $locales);


        return LocaleIdentifierCollection::fromNormalized($locales);
    }

    private function getSearchFiltersFromRequest(Request $request): array
    {
        $search = $request->get('search', null);
        if (null === $search) {
            return [];
        }

        $filters = json_decode($search, true);
        if (null === $filters) {
            throw new BadRequestHttpException('The search query parameter must be a valid JSON.');
        }

        return $filters;
    }

    private function formatSearchFilters(array $rawFilters): array
    {
        $formattedFilters = [];

        if (isset($rawFilters['complete'])) {
            $formattedFilters[] = [
                'field'    => 'complete',
                'operator' => $rawFilters['complete']['operator'],
                'value'    => $rawFilters['complete']['value'],
                'context'  => [
                    'channel' => $rawFilters['complete']['channel'],
                    'locales' => $rawFilters['complete']['locales'],
                ],
            ];
        }

        if (isset($rawFilters['updated'])) {
            $formattedFilters[] = [
                'field' => 'updated',
                'operator' => current($rawFilters['updated'])['operator'],
                'value' => current($rawFilters['updated'])['value']
            ];
        }

        return $formattedFilters;
    }
}
