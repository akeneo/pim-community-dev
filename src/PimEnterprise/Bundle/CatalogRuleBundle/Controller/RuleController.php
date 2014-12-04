<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Controller;

use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TEST
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleController extends BaseProductController
{
    /** @var RuleLinkedResourceManager */
    protected $linkedResManager;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager $linkedResManager
     */
    public function __construct(RuleLinkedResourceManager $linkedResManager)
    {
        $this->linkedResManager = $linkedResManager;
    }
    /**
     * List all rules for an attribute
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAttributeRulesAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $attributeId = $request->query->get('attributeId');

        $this->linkedResManager->getRulesForAttribute($attributeId);

        $rulesCode = $this->presentRule($attributeId);

        return new Response((string) $rulesCode);
    }

    /**
     * Return the list of rules as a string
     *
     * @param int $attributeId
     *
     * @return string
     */
    protected function presentRule($attributeId)
    {
        $rules = $this->linkedResManager->getRulesForAttribute($attributeId);

        $rulesCode = [];
        foreach ($rules as $rule) {
            $rulesCode[] = $rule->getCode();
        }

        $rulesCode = implode(", ", $rulesCode);

        return $rulesCode;
    }
}
