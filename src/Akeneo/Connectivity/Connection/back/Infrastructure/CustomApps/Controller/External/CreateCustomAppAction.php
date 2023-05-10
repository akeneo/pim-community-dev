<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommandHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
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
 *
 * @internal This is an undocumented API endpoint used for internal purposes only
 */
final class CreateCustomAppAction
{
    public function __construct(
        private readonly FeatureFlag $developerModeFeatureFlag,
        private readonly SecurityFacade $security,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly CreateCustomAppCommandHandler $createCustomAppCommandHandler,
        private readonly GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
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
        $createCommand = new CreateCustomAppCommand(
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
        if ($errors !== []) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->createCustomAppCommandHandler->handle($createCommand);
        $secret = $this->getCustomAppSecretQuery->execute($clientId);

        if (null === $secret) {
            return new JsonResponse(
                ['errors' => ['propertyPath' => null, 'message' => 'The client secret can not be retrieved.']],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse(['client_id' => $clientId, 'client_secret' => $secret], Response::HTTP_CREATED);
    }
}
