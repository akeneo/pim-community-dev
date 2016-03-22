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

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
use PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Rule controller
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleController
{
    const MASS_RULE_IMPACTED_PRODUCTS = 'rule_impacted_product_count';

    /** @var RuleDefinitionRepositoryInterface */
    protected $repository;

    /** @var RemoverInterface */
    protected $remover;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var JobLauncherInterface */
    protected $simpleJobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepo;

    /** @var OroToPimGridFilterAdapter */
    protected $gridFilterAdapter;

    /**
     * @param RuleDefinitionRepositoryInterface $repository
     * @param RemoverInterface                  $remover
     * @param TokenStorageInterface             $tokenStorage
     * @param JobLauncherInterface              $simpleJobLauncher
     * @param JobInstanceRepository             $jobInstanceRepo
     * @param OroToPimGridFilterAdapter         $gridFilterAdapter
     */
    public function __construct(
        RuleDefinitionRepositoryInterface $repository,
        RemoverInterface $remover,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $simpleJobLauncher,
        JobInstanceRepository $jobInstanceRepo,
        OroToPimGridFilterAdapter $gridFilterAdapter
    ) {
        $this->repository        = $repository;
        $this->remover           = $remover;
        $this->tokenStorage      = $tokenStorage;
        $this->simpleJobLauncher = $simpleJobLauncher;
        $this->jobInstanceRepo   = $jobInstanceRepo;
        $this->gridFilterAdapter = $gridFilterAdapter;
    }

    /**
     * List all rules
     *
     * @Template
     *
     * @return JsonResponse
     *
     * @AclAncestor("pimee_catalog_rule_rule_view_permissions")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Delete a rule
     *
     * @param int $id
     *
     * @AclAncestor("pimee_catalog_rule_rule_delete_permissions")
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function deleteAction($id)
    {
        if (null === $rule = $this->repository->find($id)) {
            throw new NotFoundHttpException(
                sprintf('Rule definition with id "%s" can not be found.', (string) $id)
            );
        }

        $this->remover->remove($rule);

        return new JsonResponse();
    }

    /**
     * Launch the mass calculation of products impacted by rules
     *
     * @AclAncestor("pimee_catalog_rule_rule_impacted_product_count_permissions")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function massImpactedProductCountAction(Request $request)
    {
        $request->request->add(['actionName' => 'massImpactedProductCount']);
        $params           = $this->gridFilterAdapter->adapt($request);
        $jobInstance      = $this->jobInstanceRepo->findOneByIdentifier(self::MASS_RULE_IMPACTED_PRODUCTS);
        $rawConfiguration = addslashes(json_encode(['ruleIds' => $params['values']]));

        $this->simpleJobLauncher->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), $rawConfiguration);

        return new JsonResponse(
            [
                'message'    => 'flash.rule.impacted_product_count',
                'successful' => true
            ]
        );
    }
}
