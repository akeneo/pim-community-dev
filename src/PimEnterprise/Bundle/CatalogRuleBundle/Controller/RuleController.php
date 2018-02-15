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
use Akeneo\Component\Console\CommandLauncher;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    const RUN_COMMAND = 'akeneo:rule:run --username=%s';

    /** @var RuleDefinitionRepositoryInterface */
    protected $repository;

    /** @var RemoverInterface */
    protected $remover;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var JobLauncherInterface */
    protected $simpleJobLauncher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $jobInstanceRepo;

    /** @var OroToPimGridFilterAdapter */
    protected $gridFilterAdapter;

    /** @var CommandLauncher */
    protected $commandLauncher;

    /**
     * @param RuleDefinitionRepositoryInterface     $repository
     * @param RemoverInterface                      $remover
     * @param TokenStorageInterface                 $tokenStorage
     * @param JobLauncherInterface                  $simpleJobLauncher
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepo
     * @param OroToPimGridFilterAdapter             $gridFilterAdapter
     * @param CommandLauncher                       $commandLauncher
     */
    public function __construct(
        RuleDefinitionRepositoryInterface $repository,
        RemoverInterface $remover,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $simpleJobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepo,
        OroToPimGridFilterAdapter $gridFilterAdapter,
        CommandLauncher $commandLauncher
    ) {
        $this->repository = $repository;
        $this->remover = $remover;
        $this->tokenStorage = $tokenStorage;
        $this->simpleJobLauncher = $simpleJobLauncher;
        $this->jobInstanceRepo = $jobInstanceRepo;
        $this->gridFilterAdapter = $gridFilterAdapter;
        $this->commandLauncher = $commandLauncher;
    }

    /**
     * List all rules
     *
     * @Template
     *
     * @AclAncestor("pimee_catalog_rule_rule_view_permissions")
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Delete a rule
     *
     * @AclAncestor("pimee_catalog_rule_rule_delete_permissions")
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

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
     * @return JsonResponse
     */
    public function massImpactedProductCountAction(Request $request)
    {
        $request->request->add(['actionName' => 'massImpactedProductCount']);
        $params = $this->gridFilterAdapter->adapt($request);
        $jobInstance = $this->jobInstanceRepo->findOneByIdentifier(self::MASS_RULE_IMPACTED_PRODUCTS);
        $configuration = ['ruleIds' => $params['values']];

        $this->simpleJobLauncher->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), $configuration);

        return new JsonResponse(
            [
                'message'    => 'flash.rule.impacted_product_count',
                'successful' => true,
            ]
        );
    }

    /**
     * Launches the execution of all existing rules in backend.
     *
     * @AclAncestor("pimee_catalog_rule_rule_execute_permissions")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function executeRulesAction(Request $request)
    {
        $command = sprintf(
            static::RUN_COMMAND,
            $this->tokenStorage->getToken()->getUsername()
        );

        $ruleCode = $request->get('code');
        if (null !== $ruleCode) {
            $command = sprintf('%s %s', $command, $ruleCode);
        }

        $this->commandLauncher->executeBackground($command);

        return new JsonResponse(
            [
                'message'    => 'flash.rule.executed',
                'successful' => true,
            ]
        );
    }
}
