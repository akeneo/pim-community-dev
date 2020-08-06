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
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class GetImpactedProductCountController
{
    /** @var ProductRuleBuilder */
    private $productRuleBuilder;

    /** @var ProductRuleSelector */
    private $productRuleSelector;

    public function __construct(
        ProductRuleBuilder $productRuleBuilder,
        ProductRuleSelector $productRuleSelector
    ) {
        $this->productRuleBuilder = $productRuleBuilder;
        $this->productRuleSelector = $productRuleSelector;
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
        } catch (\LogicException $e) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['impacted_product_count' => $subjectSet->getSubjectsCursor()->count()]);
    }
}
