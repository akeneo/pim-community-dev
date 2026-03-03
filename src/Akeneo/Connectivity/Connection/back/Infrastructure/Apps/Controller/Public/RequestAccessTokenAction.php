<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAccessTokenAction
{
    public function __construct(private FeatureFlag $featureFlag, private ValidatorInterface $validator, private CreateAccessTokenInterface $createAccessToken)
    {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }
        $accessTokenRequest = new AccessTokenRequest(
            $request->request->get('client_id', ''),
            $request->request->get('code', ''),
            $request->request->get('grant_type', ''),
            $request->request->get('code_identifier', ''),
            $request->request->get('code_challenge', '')
        );

        $violations = $this->validator->validate($accessTokenRequest);
        if ($violations->count() > 0) {
            $violation = $violations[0];
            $errorResponsePayload = ['error' => $violation->getMessage()];
            if ($violation instanceof ConstraintViolation && null !== $violationCause = $violation->getCause()) {
                $errorResponsePayload['error_description'] = $violationCause;
            }

            return new JsonResponse($errorResponsePayload, Response::HTTP_BAD_REQUEST);
        }

        $token = $this->createAccessToken->create(
            $accessTokenRequest->getClientId(),
            $accessTokenRequest->getAuthorizationCode()
        );

        return new JsonResponse($token, Response::HTTP_OK);
    }
}
