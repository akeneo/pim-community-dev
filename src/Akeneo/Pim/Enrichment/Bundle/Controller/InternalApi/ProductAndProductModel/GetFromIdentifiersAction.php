<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRows;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\LinkedProductsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetFromIdentifiersAction
{
    const MAX_RESULTS = 100;

    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        private LinkedProductsNormalizer $linkedProductsNormalizer,
        private FetchProductAndProductModelRows $fetchProductAndProductModelRows,
        private ValidatorInterface $validator
    ) {
    }

    public function __invoke(
        Request $request
    ): Response {
        if ($request->headers->get('X-Requested-With') !== 'fetch') {
            return new RedirectResponse('/');
        }

        $channelCode = $request->query->get('channel');
        $localeCode = $request->query->get('locale');
        $identifiers = json_decode($request->getContent(), true);

        $productRows = $this->findLinkedProductWithAssociations($identifiers['products'], $localeCode, $channelCode);
        $productModelRows = $this->findLinkedProductModelWithAssociations($identifiers['product_models'], $localeCode, $channelCode);
        $normalizedProducts = $this->linkedProductsNormalizer->normalize($productRows, $channelCode, $localeCode);
        $normalizedProductModels = $this->linkedProductsNormalizer->normalize($productModelRows, $channelCode, $localeCode);

        return new JsonResponse([
            'items' => array_merge($normalizedProducts, $normalizedProductModels),
            'total_count' => $productRows->totalCount() + $productModelRows->totalCount()
        ]);
    }

    private function findLinkedProductWithAssociations(
        array $productUuids,
        string $localeCode,
        string $channelCode
    ): Rows {
        $queryBuilder = $this->productQueryBuilderFactory->create(
            [
                'default_locale' => $localeCode,
                'default_scope'  => $channelCode,
                'limit' => self::MAX_RESULTS
            ]
        );

        $productUuids = array_map(
            static fn (string $productUuid) => sprintf('product_%s', $productUuid),
            $productUuids
        );

        $queryBuilder->addFilter('id', Operators::IN_LIST, $productUuids);
        $queryBuilder->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);
        $queryBuilder->addSorter('updated', 'DESC');

        $getRowsQueryParameters = new FetchProductAndProductModelRowsParameters(
            $queryBuilder,
            [],
            $channelCode,
            $localeCode
        );
        $this->checkQuery($getRowsQueryParameters);

        return ($this->fetchProductAndProductModelRows)($getRowsQueryParameters);
    }

    private function findLinkedProductModelWithAssociations(
        array $productModelCodes,
        string $localeCode,
        string $channelCode
    ): Rows {
        $queryBuilder = $this->productModelQueryBuilderFactory->create(
            [
                'default_locale' => $localeCode,
                'default_scope'  => $channelCode,
                'limit' => self::MAX_RESULTS
            ]
        );
        $queryBuilder->addFilter('identifier', Operators::IN_LIST, $productModelCodes);
        $queryBuilder->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class);
        $queryBuilder->addSorter('updated', 'DESC');

        $getRowsQueryParameters = new FetchProductAndProductModelRowsParameters(
            $queryBuilder,
            [],
            $channelCode,
            $localeCode
        );
        $this->checkQuery($getRowsQueryParameters);

        return ($this->fetchProductAndProductModelRows)($getRowsQueryParameters);
    }

    private function checkQuery(FetchProductAndProductModelRowsParameters $getRowsQueryParameters): void
    {
        $violations = $this->validator->validate($getRowsQueryParameters);
        if (0 < $violations->count()) {
            throw new \LogicException(
                'Invalid query parameters sent to fetch data in the product and product model datagrid.'
            );
        }
    }
}
