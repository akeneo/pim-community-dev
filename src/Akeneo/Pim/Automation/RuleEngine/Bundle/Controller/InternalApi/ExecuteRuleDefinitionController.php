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

use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExecuteRuleDefinitionController
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        SecurityFacade $securityFacade
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(string $ruleDefinitionCode, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('pimee_catalog_rule_rule_execute_permissions')) {
            throw new AccessDeniedException();
        }

        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleDefinitionCode);
        if (null === $ruleDefinition) {
            throw new NotFoundHttpException(sprintf('The "%s" rule definition was not found', $ruleDefinitionCode));
        }

        // Do it

        return new JsonResponse();
    }
}
