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

use Doctrine\ORM\EntityNotFoundException;
use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule controller
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleController
{
    /** @var RuleLinkedResourceManager */
    protected $linkedResManager;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager $linkedResManager
     * @param NormalizerInterface       $normalizer
     * @param string                    $attributeClass
     */
    public function __construct(
        RuleLinkedResourceManager $linkedResManager,
        NormalizerInterface $normalizer,
        $attributeClass
    ) {
        $this->linkedResManager = $linkedResManager;
        $this->normalizer       = $normalizer;
        $this->attributeClass   = $attributeClass;
    }

    public function indexAction($resourceType, $resourceId)
    {
        switch ($resourceType) {
            case 'attribute':
                $resourceName = $this->attributeClass;
                break;
            default:
                $resourceName = '';
        }

        $rules = $this->linkedResManager->getRulesForAttribute($resourceId, $resourceName);

        $normalizedRules = $this->normalizer->normalize($rules, 'array');

        return new JsonResponse($normalizedRules);
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
