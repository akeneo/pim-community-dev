<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 */
class AssetCategoryController
{
    /** @var ApiResourceRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ApiResourceRepositoryInterface $repository
     * @param NormalizerInterface            $normalizer
     */
    public function __construct(ApiResourceRepositoryInterface $repository, NormalizerInterface $normalizer)
    {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_category_list")
     */
    public function getAction(Request $request, string $code): Response
    {
        $assetCategory = $this->repository->findOneByIdentifier($code);
        if (null === $assetCategory) {
            throw new NotFoundHttpException(sprintf('Asset category "%s" does not exist.', $code));
        }

        $assetCategoryApi = $this->normalizer->normalize($assetCategory, 'external_api');

        return new JsonResponse($assetCategoryApi);
    }
}
