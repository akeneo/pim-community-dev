<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class AssetTagController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetTagRepository;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param IdentifiableObjectRepositoryInterface $assetTagRepository
     * @param ParameterValidatorInterface           $parameterValidator
     * @param PaginatorInterface                    $paginator
     * @param array                                 $apiConfiguration
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $assetTagRepository,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $paginator,
        array $apiConfiguration
    ) {
        $this->assetTagRepository = $assetTagRepository;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Request $request
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function getAction(Request $request, string $code): Response
    {
        $assetTag = $this->assetTagRepository->findOneByIdentifier($code);

        if (null === $assetTag) {
            throw new NotFoundHttpException(sprintf('Tag "%s" does not exist.', $code));
        }

        return new JsonResponse(['code' => $assetTag->getCode()]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        try {
            $this->parameterValidator->validate($request->query->all());
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $defaultParameters = [
            'page'       => 1,
            'limit'      => $this->apiConfiguration['pagination']['limit_by_default'],
            'with_count' => 'false',
        ];

        $queryParameters = array_merge($defaultParameters, $request->query->all());

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $assetTags = $this->assetTagRepository->searchAfterOffset([], ['code' => 'ASC'], $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pimee_api_asset_tag_list',
            'item_route_name'  => 'pimee_api_asset_tag_get',
        ];

        $assetTagsNormalized = [];
        foreach ($assetTags as $assetTag){
            $assetTagsNormalized[] = ['code' => $assetTag->getCode()];
        }

        $count = true === $request->query->getBoolean('with_count') ? $this->assetTagRepository->count() : null;
        $paginatedAssetTags = $this->paginator->paginate(
            $assetTagsNormalized,
            $parameters,
            $count
        );

        return new JsonResponse($paginatedAssetTags);
    }
}
