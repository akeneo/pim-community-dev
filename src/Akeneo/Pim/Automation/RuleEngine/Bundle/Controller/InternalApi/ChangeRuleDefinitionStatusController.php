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

use Akeneo\Pim\Automation\RuleEngine\Component\Command\UpdateRuleStatusCommand;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\RuleNotFoundException;
use Akeneo\Pim\Automation\RuleEngine\Component\Handler\UpdateRuleStatusHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ChangeRuleDefinitionStatusController
{
    /** @var UpdateRuleStatusHandler */
    private $updateRuleStatusHandler;

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        UpdateRuleStatusHandler $updateRuleStatusHandler,
        SecurityFacade $securityFacade
    ) {
        $this->updateRuleStatusHandler = $updateRuleStatusHandler;
        $this->securityFacade = $securityFacade;
    }

    public function __invoke(string $code, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('pimee_catalog_rule_rule_edit_permissions')) {
            throw new AccessDeniedException();
        }

        $data = \json_decode($request->getContent(), true);
        if (!is_bool($data['enabled'] ?? null)) {
            throw new BadRequestHttpException("The 'enabled' key must be provided and must be a boolean");
        }

        $command = new UpdateRuleStatusCommand($code, $data['enabled']);
        try {
            ($this->updateRuleStatusHandler)($command);
        } catch (RuleNotFoundException $e) {
            return new JsonResponse([
                'code'    => Response::HTTP_NOT_FOUND,
                'message' => sprintf('The "%s" rule definition was not found', $code),
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
