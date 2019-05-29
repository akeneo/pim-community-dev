<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Controller\Product;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRows;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer\LinkedProductNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Get products linked to a record on an attribute
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class GetProductsLinkedToARecordAction
{
    private const MAX_RESULTS = 20;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var FetchProductAndProductModelRows */
    private $fetchProductAndProductModelRows;

    /** @var ValidatorInterface */
    private $validator;

    /** @var LinkedProductNormalizer */
    private $linkedProductNormalizer;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        FetchProductAndProductModelRows $fetchProductAndProductModelRows,
        ValidatorInterface $validator,
        LinkedProductNormalizer $linkedProductNormalizer
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->fetchProductAndProductModelRows = $fetchProductAndProductModelRows;
        $this->linkedProductNormalizer = $linkedProductNormalizer;
        $this->validator = $validator;
    }

    public function __invoke(Request $request, string $recordCode, string $attributeCode): JsonResponse
    {
        $channelCode = $request->query->get('channel');
        $localeCode = $request->query->get('locale');

        $rows = $this->findProductAndProductModelsIdentifiers($recordCode, $attributeCode, $localeCode, $channelCode);
        $normalizedProducts = $this->normalizeProducts($rows, $localeCode);

        return new JsonResponse(['items' => $normalizedProducts, 'total_count' => $rows->totalCount()]);
    }

    private function findProductAndProductModelsIdentifiers(
        string $recordCode,
        string $attributeCode,
        string $localeCode,
        string $channelCode
    ): Rows {
        $queryBuilder = $this->pqbFactory->create(
            [
                'default_locale' => $localeCode,
                'default_scope'  => $channelCode,
                'limit' => self::MAX_RESULTS
            ]
        );
        $queryBuilder->addFilter($attributeCode, Operators::IN_LIST, [$recordCode]);
        $queryBuilder->addSorter('updated', 'DESC');

        $getRowsQueryParameters = new FetchProductAndProductModelRowsParameters(
            $queryBuilder,
            [],
            $channelCode,
            $localeCode
        );
        $this->checkQuery($getRowsQueryParameters);
        $rows = ($this->fetchProductAndProductModelRows)($getRowsQueryParameters);

        return $rows;
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

    private function normalizeProducts(Rows $rows, string $localeCode): array
    {
        $normalizedProducts = [];
        foreach ($rows->rows() as $index => $row) {
            $normalizedProducts[] = $this->linkedProductNormalizer->normalize($row, $localeCode);
            if (self::MAX_RESULTS === $index + 1) {
                break;
            }
        }

        return $normalizedProducts;
    }
}
