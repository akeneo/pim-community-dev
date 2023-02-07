<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppSecretQueryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCustomAppSecretAction
{
    public function __construct(
        private readonly SecurityFacade $security,
        private readonly GetTestAppQueryInterface $getTestAppQuery,
        private readonly GetTestAppSecretQueryInterface $getTestAppSecretQuery,
    ) {
    }

    public function __invoke(Request $request, string $customAppId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }

        $customApp = $this->getTestAppQuery->execute($customAppId);
        if (null === $customApp) {
            throw new NotFoundHttpException(\sprintf('Custom app with id %s was not found.', $customAppId));
        }

        $secret = $this->getTestAppSecretQuery->execute($customAppId);

        $secretObfuscated = \str_pad(
            string: \substr($secret, -4),
            length: 34,
            pad_string: '*',
            pad_type: STR_PAD_LEFT
        );

        return new JsonResponse($secretObfuscated);
    }
}
