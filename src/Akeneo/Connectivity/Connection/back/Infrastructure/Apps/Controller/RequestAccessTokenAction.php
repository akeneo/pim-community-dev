<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAccessTokenAction
{
    private FeatureFlag $featureFlag;
    private ValidatorInterface $validator;
    private CreateAccessTokenInterface $createAccessToken;

    public function __construct(
        FeatureFlag $featureFlag,
        ValidatorInterface $validator,
        CreateAccessTokenInterface $createAccessToken
    ) {
        $this->featureFlag = $featureFlag;
        $this->validator = $validator;
        $this->createAccessToken = $createAccessToken;
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        $accessTokenRequest = new AccessTokenRequest(
            $request->get('client_id', ''),
            $request->get('code', ''),
            $request->get('grant_type', ''),
            $request->get('code_identifier', ''),
            $request->get('code_challenge', '')
        );
        $violations = $this->validator->validate($accessTokenRequest);
        if ($violations->count() > 0) {
            return new JsonResponse(
                ['error' => $violations[0]->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        //check en base si pim_api_auth_code scope === openid pour le
        //            $accessTokenRequest->getAuthorizationCode() === token
        //context =openid => create jwt
        //return   { "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjFlOWdkazcifQ.ewogImlzc
        //     yI6ICJodHRwOi8vc2VydmVyLmV4YW1wbGUuY29tIiwKICJzdWIiOiAiMjQ4Mjg5
        //     NzYxMDAxIiwKICJhdWQiOiAiczZCaGRSa3F0MyIsCiAibm9uY2UiOiAibi0wUzZ
        //     fV3pBMk1qIiwKICJleHAiOiAxMzExMjgxOTcwLAogImlhdCI6IDEzMTEyODA5Nz
        //     AKfQ.ggW8hZ1EuVLuxNuuIJKX_V8a_OMXzR0EHR9R6jgdqrOOF4daGU96Sr_P6q
        //     Jp6IcmD3HP99Obi1PRs-cwh3LO-p146waJ8IhehcwL7F09JdijmBqkvPeB2T9CJ
        //     NqeGpe-gccMg4vfKjkM8FcGvnzZUN4_KSP0aAp1tOJ1zZwgjxqGByKHiOtX7Tpd
        //     QyHE5lcMiKPXfEIQILVq0pc_E2DzL7emopWoaoZTF_m0_N0YzFC6g6EJbOEoRoS
        //     K5hoDalrcvRYLSrQAZZKflyuVCyixEoV9GfNQC3_osjzw2PAithfubEEBLuVVk4
        //     XUVrWOLrLl0nx7RkKU8NXNHq-rvKMzqg"
        //  }

        $token = $this->createAccessToken->create(
            $accessTokenRequest->getClientId(),
            $accessTokenRequest->getAuthorizationCode()
        );

        return new JsonResponse($token, Response::HTTP_OK);
    }
}
