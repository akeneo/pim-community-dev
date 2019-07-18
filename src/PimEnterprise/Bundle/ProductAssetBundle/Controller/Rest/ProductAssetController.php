<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Controller\Rest;

use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Asset rest controller
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductAssetController
{
    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var NormalizerInterface */
    protected $assetNormalizer;

    /**
     * @param AssetRepositoryInterface $assetRepository
     * @param NormalizerInterface      $assetNormalizer
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        NormalizerInterface $assetNormalizer
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetNormalizer = $assetNormalizer;
    }

    /**
     * Assets index action
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $options = $request->get('options');

        if ($request->query->has('identifiers') || $request->request->has('identifiers')) {
            $options['identifiers'] = explode(',', $request->get('identifiers'));
        }

        $assets = $this->assetRepository->findEntitiesBySearch(
            $request->get('search'),
            $options
        );

        $normalizedAssets = array_map(function ($asset) {
            return $this->assetNormalizer->normalize($asset, 'internal_api');
        }, $assets);

        return new JsonResponse($normalizedAssets);
    }
}
