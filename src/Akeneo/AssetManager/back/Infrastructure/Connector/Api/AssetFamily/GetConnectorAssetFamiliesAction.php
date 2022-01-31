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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyQuery;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyItemsInterface;
use Akeneo\AssetManager\Domain\Query\Limit;
use Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\Hal\AddHalDownloadLinkToAssetFamilyImage;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorAssetFamiliesAction
{
    private Limit $limit;

    public function __construct(
        private FindConnectorAssetFamilyItemsInterface $findConnectorAssetFamilyItems,
        private PaginatorInterface $halPaginator,
        private AddHalDownloadLinkToAssetFamilyImage $addHalDownloadLinkToImage,
        int $limit,
        private SecurityFacadeInterface $securityFacade,
    ) {
        $this->limit = new Limit($limit);
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

        try {
            $searchAfter = $request->get('search_after', null);
            $searchAfterIdentifier = null !== $searchAfter ? AssetFamilyIdentifier::fromString($searchAfter) : null;
            $assetFamilyQuery = AssetFamilyQuery::createPaginatedQuery($this->limit->intValue(), $searchAfterIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $assetFamilies = $this->findConnectorAssetFamilyItems->find($assetFamilyQuery);
        $assetFamilies = array_map(function (ConnectorAssetFamily $assetFamily) {
            $normalizedAssetFamily = $assetFamily->normalize();

            /** /!\ /!\ /!\ /!\
             * Crappy tricks to only remove the image of the asset family on the API side....
             * @todo : To remove if the functional decide to not have an image on the asset family
             * @todo : Check the PR https://github.com/akeneo/pim-enterprise-dev/pull/6651 for real fix
             */
            if (array_key_exists('image', $normalizedAssetFamily)) {
                unset($normalizedAssetFamily['image']);
            }

            return ($this->addHalDownloadLinkToImage)($normalizedAssetFamily);
        }, $assetFamilies);

        $paginatedAssetFamilies = $this->paginateAssetFamilies($assetFamilies, $searchAfter);

        return new JsonResponse($paginatedAssetFamilies);
    }

    private function paginateAssetFamilies(array $assetFamilies, ?string $searchAfter): array
    {
        $lastAssetFamily = end($assetFamilies);
        reset($assetFamilies);
        $lastAssetFamilyCode = $lastAssetFamily['code'] ?? null;

        $paginationParameters = [
            'list_route_name'     => 'akeneo_asset_manager_asset_families_rest_connector_get',
            'item_route_name'     => 'akeneo_asset_manager_asset_family_rest_connector_get',
            'search_after'        => [
                'self' => $searchAfter,
                'next' => $lastAssetFamilyCode
            ],
            'limit'               => $this->limit->intValue(),
            'item_identifier_key' => 'code',
            'query_parameters'    => [],
        ];

        return $this->halPaginator->paginate($assetFamilies, $paginationParameters, count($assetFamilies));
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_asset_family_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list asset families.');
        }
    }
}
