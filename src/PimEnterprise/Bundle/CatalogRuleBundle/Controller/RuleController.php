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
 * Rule controller
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
     * @param string $attributeId
     *
     * @return Response
     */
    public function listAttributeRulesAction($attributeId)
    {
        $ruleCodes = $this->presentRule($attributeId);

        return new Response($ruleCodes);
    }

    /**
     * Return the list of rules as a string
     *
     * TODO: use the future rule presenter
     *
     * @param int $attributeId
     *
     * @return string
     */
    protected function presentRule($attributeId)
    {
        $rules = $this->linkedResManager->getRulesForAttribute($attributeId);

        $ruleCodes = [];
        foreach ($rules as $rule) {
            $ruleCodes[] = $rule->getCode();
        }

        $ruleCodes = implode(", ", $ruleCodes);

        return $ruleCodes;
    }
}
