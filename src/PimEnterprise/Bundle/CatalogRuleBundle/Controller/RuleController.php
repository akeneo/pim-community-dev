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

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleRelationManager;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule controller
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleController
{
    /** @var RuleRelationManager */
    protected $ruleRelationManager;

    /** @var RemoverInterface */
    protected $ruleRemover;

    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleDefinitionRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * Constructor
     *
     * @param RuleRelationManager               $ruleRelationManager
     * @param RemoverInterface                  $ruleRemover
     * @param RuleDefinitionRepositoryInterface $ruleDefinitionRepo
     * @param NormalizerInterface               $normalizer
     */
    public function __construct(
        RuleRelationManager $ruleRelationManager,
        RemoverInterface $ruleRemover,
        RuleDefinitionRepositoryInterface $ruleDefinitionRepo,
        NormalizerInterface $normalizer
    ) {
        $this->ruleRelationManager = $ruleRelationManager;
        $this->ruleRemover        = $ruleRemover;
        $this->ruleDefinitionRepo = $ruleDefinitionRepo;
        $this->normalizer         = $normalizer;
    }

    /**
     * List all rules for the given resource
     * @param string $resourceName
     * @param int    $resourceId
     *
     * @return JsonResponse
     *
     * @AclAncestor("pimee_catalog_rule_rule_view_permissions")
     */
    public function indexAction($resourceName, $resourceId)
    {
        $rules = $this->ruleRelationManager->getRulesForResource($resourceId, $resourceName);
        $normalizedRules = $this->normalizer->normalize($rules, 'array');

        return new JsonResponse($normalizedRules);
    }

    /**
     * Delete an rule of a resource
     *
     * @param string $resourceName
     * @param int    $resourceId
     * @param int    $ruleId
     *
     * @AclAncestor("pimee_catalog_rule_rule_view_permissions")
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function deleteAction($resourceName, $resourceId, $ruleId)
    {
        if (null === $rule = $this->ruleDefinitionRepo->find($ruleId)) {
            throw new NotFoundHttpException(
                sprintf('Rule definition with id "%s" can not be found.', (string) $ruleId)
            );
        }

        $this->ruleRemover->remove($rule);

        return new JsonResponse();
    }
}
