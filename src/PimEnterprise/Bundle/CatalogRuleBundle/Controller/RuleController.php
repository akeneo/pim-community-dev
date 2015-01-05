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

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\RuleEngineBundle\Manager\RuleDefinitionManager;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule controller
 *
 * TODO: this controller should not include attribute references
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleController
{
    /** @var RuleLinkedResourceManager */
    protected $linkedResManager;

    /** @var RuleDefinitionManager */
    protected $ruleManager;

    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleDefinitionRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager         $linkedResManager
     * @param RuleDefinitionManager             $ruleManager
     * @param RuleDefinitionRepositoryInterface $ruleDefinitionRepo
     * @param NormalizerInterface               $normalizer
     * @param string                            $attributeClass
     */
    public function __construct(
        RuleLinkedResourceManager $linkedResManager,
        RuleDefinitionManager $ruleManager,
        RuleDefinitionRepositoryInterface $ruleDefinitionRepo,
        NormalizerInterface $normalizer,
        $attributeClass
    ) {
        $this->linkedResManager   = $linkedResManager;
        $this->ruleManager        = $ruleManager;
        $this->ruleDefinitionRepo = $ruleDefinitionRepo;
        $this->normalizer         = $normalizer;

        // TODO: Should be inejected with an "- calls:" in DI
        $this->attributeClass     = $attributeClass;
    }

    /**
     * List all rules for the given resource
     * @param string $resourceType
     * @param int    $resourceId
     *
     * @return JsonResponse
     *
     * @AclAncestor("pimee_catalog_rule_rule_view_permissions")
     */
    public function indexAction($resourceType, $resourceId)
    {
        // TODO improvement: Use a class mapping with an addMappedClass and an array to be more extandable
        switch ($resourceType) {
            case 'attribute':
                $resourceName = $this->attributeClass;
                break;
            default:
                throw new NotFoundHttpException(sprintf('Resource type %s is unknown', $resourceType));

        }

        // TODO: getRulesForResource
        $rules = $this->linkedResManager->getRulesForAttribute($resourceId, $resourceName);

        $normalizedRules = $this->normalizer->normalize($rules, 'array');

        return new JsonResponse($normalizedRules);
    }

    /**
     * Delete an rule of a resource
     *
     * // TODO : remove unused parameters
     *
     * @param string $resourceType
     * @param int    $resourceId
     * @param int    $ruleId
     *
     * @AclAncestor("pimee_catalog_rule_rule_view_permissions")
     *
     * @return JsonResponse
     */
    public function deleteAction($resourceType, $resourceId, $ruleId)
    {
        $rule = $this->ruleDefinitionRepo->findOneById($ruleId);
        //TODO if rule null, throw 404

        try {
            $this->ruleManager->remove($rule);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occured during the deletion of the rule.'], 500);
        }

        return new JsonResponse();
    }
}
