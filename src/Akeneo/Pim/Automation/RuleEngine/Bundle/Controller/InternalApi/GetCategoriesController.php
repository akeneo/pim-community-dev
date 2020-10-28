<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GetCategoriesController
{
    /** @var CategoryRepositoryInterface */
    protected $repository;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var NormalizerInterface */
    protected $normalizer;

    public function __construct(
        CategoryRepositoryInterface $repository,
        CollectionFilterInterface $collectionFilter,
        NormalizerInterface $normalizer
    ) {
        $this->repository = $repository;
        $this->collectionFilter = $collectionFilter;
        $this->normalizer = $normalizer;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $categoryCodes = $request->get('identifiers');

        $categories = $this->repository->findBy(['code' => $categoryCodes]);
        $categories = $this->collectionFilter->filterCollection($categories, 'pim.internal_api.product_category.view');

        return new JsonResponse($this->normalizer->normalize($categories, 'internal_api', ['with_root' => true]));
    }
}
