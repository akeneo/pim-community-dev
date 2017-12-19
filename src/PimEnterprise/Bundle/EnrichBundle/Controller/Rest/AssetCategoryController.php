<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller\Rest;

use PimEnterprise\Component\ProductAsset\Repository\AssetCategoryRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AssetCategoryController
{
    /** @var AssetCategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param AssetCategoryRepositoryInterface      $categoryRepository
     * @param NormalizerInterface                   $normalizer
     */
    public function __construct(
        AssetCategoryRepositoryInterface $categoryRepository,
        NormalizerInterface $normalizer
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * List root categories
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $categories = $this->categoryRepository->findRoot();
        $normalizedCategories = [];
        foreach ($categories as $category) {
            $normalizedCategories[] = $this->normalizer->normalize($category, 'standard');
        }

        return new JsonResponse($normalizedCategories);
    }
}
