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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SearchProductsAndModelsController
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    public function __construct(ProductQueryBuilderFactoryInterface $pqbFactory)
    {
        $this->pqbFactory = $pqbFactory;
    }

    public function __invoke(Request $request): Response
    {
        $search = $request->get('search', null);
        $options = $request->get('options', []);
        $type = $options['type'] ?? null;
        $page = $options['page'] ?? 0;
        $limit = (int)$options['limit'] ?? 20;

        $pqb = $this->pqbFactory->create(['limit' => $limit, 'from' => $page * $limit]);
        if (null !== $search) {
            $pqb->addFilter('identifier', Operators::CONTAINS, $search);
        }

        if ('product' === $type) {
            $pqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);
        } elseif ('product_model' === $type) {
            $pqb->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class);
        }

        $identifiers = $pqb->execute();
        $results = [];
        foreach ($identifiers as $identifier) {
            $results[] = [
                'id' => $identifier->getIdentifier(),
                'text' => $identifier->getIdentifier(),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
}
