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

use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleBuilder;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleSelector;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\ProductAndProductsModelDocumentTypeFacetFactory;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultAwareInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class GetImpactedProductCountController
{
    private ProductRuleBuilder $productRuleBuilder;
    private ProductRuleSelector $productRuleSelector;
    private ProductAndProductsModelDocumentTypeFacetFactory $productAndProductsModelDocumentTypeFacetFactory;

    public function __construct(
        ProductRuleBuilder $productRuleBuilder,
        ProductRuleSelector $productRuleSelector,
        ProductAndProductsModelDocumentTypeFacetFactory $productAndProductsModelDocumentTypeFacetFactory
    ) {
        $this->productRuleBuilder = $productRuleBuilder;
        $this->productRuleSelector = $productRuleSelector;
        $this->productAndProductsModelDocumentTypeFacetFactory = $productAndProductsModelDocumentTypeFacetFactory;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $conditions = \json_decode($request->get('conditions'), true);
        if ($conditions === null && \json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $ruleDefinition = new RuleDefinition();
        $ruleDefinition->setCode('fake');
        $ruleDefinition->setContent(['conditions' => $conditions, 'actions' => []]);

        try {
            $rule = $this->productRuleBuilder->build($ruleDefinition);
            $subjectSet = $this->productRuleSelector->select($rule);

            Assert::isInstanceOf($subjectSet->getSubjectsCursor(), ResultAwareInterface::class);
            $documentTypeFacet = null;
            $documentTypeFacet = $this->productAndProductsModelDocumentTypeFacetFactory->build(
                $subjectSet->getSubjectsCursor()->getResult()
            );
            Assert::notNull($documentTypeFacet);

            return new JsonResponse([
                'impacted_product_count' => $documentTypeFacet->getCountForKey(ProductInterface::class),
                'impacted_product_model_count' => $documentTypeFacet->getCountForKey(ProductModelInterface::class),
            ]);
        } catch (\LogicException | \InvalidArgumentException $e) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }
}
