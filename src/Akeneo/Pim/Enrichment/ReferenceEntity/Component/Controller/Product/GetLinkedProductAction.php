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

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Get the first 16 products linked to a record on an attribute
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class GetLinkedProductAction
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
      ProductQueryBuilderFactoryInterface $pqbFactory,
      NormalizerInterface $normalizer
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->normalizer = $normalizer;
    }

    public function __invoke(Request $request, string $recordCode, string $attributeCode): JsonResponse
    {
        $recordCode = $this->getRecordCodeOr404($recordCode);
        $queryBuilder = $this->pqbFactory->create([
            'default_locale' => $request->query->get('locale'),
            'default_scope' => $request->query->get('channel')
        ]);

        $queryBuilder->addFilter($attributeCode, Operators::IN_LIST, [(string) $recordCode]);
        $products = $queryBuilder->execute();

        $normalizedProducts = [];
        foreach ($products as $index => $product) {
            $normalizedProducts[] = $this->normalizer->normalize($product, 'internal_api', []);
            if ($index >= 16) {
                break;
            }
        }

        return new JsonResponse($normalizedProducts);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getRecordCodeOr404(string $recordCode): RecordCode
    {
        try {
            return RecordCode::fromString($recordCode);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
