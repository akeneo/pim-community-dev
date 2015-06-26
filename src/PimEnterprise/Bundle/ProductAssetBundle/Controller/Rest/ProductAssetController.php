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
    public function __construct(AssetRepositoryInterface $assetRepository, NormalizerInterface $assetNormalizer)
    {
        $this->assetRepository = $assetRepository;
        $this->assetNormalizer = $assetNormalizer;
    }

    /**
     * Assets index action
     *
     * @param string $identifiers
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $identifiers = $request->get('identifiers');
        $identifiers = '' !== $identifiers ? explode(',', $identifiers) : [];

        if (0 === count($identifiers)) {
            $assets = $this->assetRepository->findAll();
        } else {
            $assets = $this->assetRepository->findByIdentifiers($identifiers);
        }

        return new JsonResponse($this->assetNormalizer->normalize($assets, 'structured'));
    }
}
