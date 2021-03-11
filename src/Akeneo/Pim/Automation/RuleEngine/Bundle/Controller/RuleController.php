<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Controller;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\OroToPimGridFilterAdapter;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
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
    protected const MASS_RULE_IMPACTED_PRODUCTS = 'rule_impacted_product_count';
    protected const RULE_EXECUTION_JOB = 'rule_engine_execute_rules';

    protected RuleDefinitionRepositoryInterface $repository;
    protected RemoverInterface $remover;
    protected TokenStorageInterface $tokenStorage;
    protected JobLauncherInterface $jobLauncher;
    protected IdentifiableObjectRepositoryInterface $jobInstanceRepo;
    protected OroToPimGridFilterAdapter $gridFilterAdapter;
    protected MassActionParametersParser $parameterParser;

    public function __construct(
        RuleDefinitionRepositoryInterface $repository,
        RemoverInterface $remover,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepo,
        OroToPimGridFilterAdapter $gridFilterAdapter,
        MassActionParametersParser $parameterParser
    ) {
        $this->repository = $repository;
        $this->remover = $remover;
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepo = $jobInstanceRepo;
        $this->gridFilterAdapter = $gridFilterAdapter;
        $this->parameterParser = $parameterParser;
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
    public function deleteAction(Request $request, $id): Response
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
    public function massImpactedProductCountAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $request->request->add(['actionName' => 'massImpactedProductCount']);
        $parameters = $this->parameterParser->parse($request);
        $filters = $this->gridFilterAdapter->adapt($parameters);
        $jobInstance = $this->jobInstanceRepo->findOneByIdentifier(self::MASS_RULE_IMPACTED_PRODUCTS);
        $user = $this->tokenStorage->getToken()->getUser();

        $configuration = [
            'ruleIds' => $filters['values'],
            'user_to_notify' => $user->getUsername(),
        ];

        $this->jobLauncher->launch($jobInstance, $user, $configuration);

        return new JsonResponse(
            [
                'message' => 'flash.rule.impacted_product_count',
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
    public function executeRulesAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $ruleCode = $request->get('code');
        if (null !== $ruleCode) {
            $ruleCode = [$ruleCode];
        }

        $jobInstance = $this->jobInstanceRepo->findOneByIdentifier(static::RULE_EXECUTION_JOB);
        $user = $user = $this->tokenStorage->getToken()->getUser();

        $configuration = [
            'rule_codes' => $ruleCode,
            'user_to_notify' => $user->getUsername(),
        ];

        $this->jobLauncher->launch($jobInstance, $user, $configuration);

        return new JsonResponse(
            [
                'message' => 'flash.rule.executed',
                'successful' => true,
            ]
        );
    }
}
