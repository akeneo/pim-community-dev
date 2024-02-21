<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCustomAppSecretAction
{
    public function __construct(
        private readonly GetCustomAppQueryInterface $getCustomAppQuery,
        private readonly GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
    ) {
    }

    public function __invoke(Request $request, string $customAppId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $customApp = $this->getCustomAppQuery->execute($customAppId);
        if (null === $customApp) {
            throw new NotFoundHttpException(\sprintf('Custom app with id %s was not found.', $customAppId));
        }

        $secret = $this->getCustomAppSecretQuery->execute($customAppId);

        $secretObfuscated = \str_pad(
            string: \substr($secret, -4),
            length: 34,
            pad_string: '*',
            pad_type: STR_PAD_LEFT
        );

        return new JsonResponse($secretObfuscated);
    }
}
