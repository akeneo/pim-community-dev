<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommandHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppSecretQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTestAppAction
{
    public function __construct(
        private FeatureFlag $appDevModeFeatureFlag,
        private FeatureFlag $marketplaceActivateFeatureFlag,
        private ValidatorInterface $validator,
        private TokenStorageInterface $tokenStorage,
        private CreateTestAppCommandHandler $createTestAppCommandHandler,
        private GetTestAppSecretQueryInterface $getTestAppSecret,
        private SecurityFacade $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->appDevModeFeatureFlag->isEnabled() || !$this->marketplaceActivateFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof UserInterface) {
            return new JsonResponse('Invalid user token.', Response::HTTP_BAD_REQUEST);
        }

        $clientId = Uuid::uuid4()->toString();
        $createCommand = new CreateTestAppCommand(
            $clientId,
            $request->get('name', ''),
            $request->get('activateUrl', ''),
            $request->get('callbackUrl', ''),
            $user->getId(),
        );
        $violations = $this->validator->validate($createCommand);
        if ($violations->count() !== 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->createTestAppCommandHandler->handle($createCommand);
        $secret = $this->getTestAppSecret->execute($clientId);

        if (null === $secret) {
            return new JsonResponse(
                ['errors' => ['propertyPath' => null, 'message' => 'The client secret can not be retrieved.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse(['clientId' => $clientId, 'clientSecret' => $secret], Response::HTTP_CREATED);
    }
}
