<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommandHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppSecretQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTestAppAction
{
    public function __construct(
        private FeatureFlag $developerModeFeatureFlag,
        private SecurityFacade $security,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
        private TokenStorageInterface $tokenStorage,
        private CreateTestAppCommandHandler $createTestAppCommandHandler,
        private GetTestAppSecretQueryInterface $getTestAppSecretQuery,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->developerModeFeatureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof UserInterface) {
            throw new BadRequestHttpException('Invalid user token.');
        }

        $clientId = Uuid::uuid4()->toString();
        $createCommand = new CreateTestAppCommand(
            $clientId,
            (string) $request->get('name', ''),
            (string) $request->get('activate_url', ''),
            (string) $request->get('callback_url', ''),
            $user->getId(),
        );

        $errors = [];
        foreach ($this->validator->validate($createCommand) as $violation) {
            $snakeCasedPropertyPath = \strtolower(\preg_replace("/[A-Z]/", '_$0', $violation->getPropertyPath()));
            $translatedMessage = $this->translator->trans($violation->getMessage(), [], 'jsmessages');
            $errors[] = [
                'propertyPath' => $snakeCasedPropertyPath,
                'message' => $translatedMessage,
            ];
        }
        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->createTestAppCommandHandler->handle($createCommand);
        $secret = $this->getTestAppSecretQuery->execute($clientId);

        if (null === $secret) {
            return new JsonResponse(
                ['errors' => ['propertyPath' => null, 'message' => 'The client secret can not be retrieved.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse(['client_id' => $clientId, 'client_secret' => $secret], Response::HTTP_CREATED);
    }
}
