<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\DBAL\Connection;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
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
    private Connection $connection;
    private string $pimUrl;

    public function __construct(
        FeatureFlag $featureFlag,
        ValidatorInterface $validator,
        CreateAccessTokenInterface $createAccessToken,
        Connection $connection,
        string $pimUrl
    ) {
        $this->featureFlag = $featureFlag;
        $this->validator = $validator;
        $this->createAccessToken = $createAccessToken;
        $this->connection = $connection;
        $this->pimUrl = $pimUrl;
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
        $query = 'select scope, user_id from akeneo_pim.pim_api_auth_code where token = :token limit 1';
        $result = $this->connection->fetchAssoc($query, [
            'token' => $accessTokenRequest->getAuthorizationCode(),
        ]);

        $jwtToken = null;
        if (is_array($result)){
            if($result['scope'] === 'openid')
            {
                $query = 'select email, first_name, last_name from akeneo_pim.oro_user where id = :id limit 1';
                $user = $this->connection->fetchAssoc($query, [
                    'id' => $result['user_id'],
                ]);
                $email = $user['email'];
                $firstname = $user['first_name'];
                $lastname = $user['last_name'];

                //TODO inject as env var
                $privateKeyAsText = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQC8kGa1pSjbSYZVebtTRBLxBz5H4i2p/llLCrEeQhta5kaQu/Rn
vuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t0tyazyZ8JXw+KgXTxldMPEL9
5+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4ehde/zUxo6UvS7UrBQIDAQAB
AoGAb/MXV46XxCFRxNuB8LyAtmLDgi/xRnTAlMHjSACddwkyKem8//8eZtw9fzxz
bWZ/1/doQOuHBGYZU8aDzzj59FZ78dyzNFoF91hbvZKkg+6wGyd/LrGVEB+Xre0J
Nil0GReM2AHDNZUYRv+HYJPIOrB0CRczLQsgFJ8K6aAD6F0CQQDzbpjYdx10qgK1
cP59UHiHjPZYC0loEsk7s+hUmT3QHerAQJMZWC11Qrn2N+ybwwNblDKv+s5qgMQ5
5tNoQ9IfAkEAxkyffU6ythpg/H0Ixe1I2rd0GbF05biIzO/i77Det3n4YsJVlDck
ZkcvY3SK2iRIL4c9yY6hlIhs+K9wXTtGWwJBAO9Dskl48mO7woPR9uD22jDpNSwe
k90OMepTjzSvlhjbfuPN1IdhqvSJTDychRwn1kIJ7LQZgQ8fVz9OCFZ/6qMCQGOb
qaGwHmUK6xzpUbbacnYrIM6nLSkXgOAwv7XXCojvY614ILTK3iXiLBOxPu5Eu13k
eUz9sHyD6vkgZzjtxXECQAkp4Xerf5TGfQXGXhxIX52yH+N2LtujCdkQZjXAsGdm
B2zNzvrlgRmgBrklMTrMYgm1NPcW+bRLGcwgW2PTvNM=
-----END RSA PRIVATE KEY-----
EOD;

                //TODO inject as env var
                $publicKeyAsText = <<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8kGa1pSjbSYZVebtTRBLxBz5H
4i2p/llLCrEeQhta5kaQu/RnvuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t
0tyazyZ8JXw+KgXTxldMPEL95+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4
ehde/zUxo6UvS7UrBQIDAQAB
-----END PUBLIC KEY-----
EOD;

                $privateKey = InMemory::plainText($privateKeyAsText);
                $publicKey = InMemory::plainText($publicKeyAsText);

                $jwtConfig = Configuration::forAsymmetricSigner(
                    new Sha256(),
                    $privateKey,
                    $publicKey
                );

                $now = new \DateTimeImmutable();
                $jwtToken = $jwtConfig->builder()
                    ->issuedBy($this->pimUrl)
                    ->identifiedBy('?') //TODO what to put in it ?
                    ->issuedAt($now)
                    ->canOnlyBeUsedAfter($now->modify('+1 second'))
                    ->expiresAt($now->modify('+1 hour'))
                    ->withClaim('email', $email)
                    ->withClaim('firstname', $firstname)
                    ->withClaim('lastname', $lastname)
                    ->getToken($jwtConfig->signer(), $jwtConfig->signingKey());
            }
        }

        $token = $this->createAccessToken->create(
            $accessTokenRequest->getClientId(),
            $accessTokenRequest->getAuthorizationCode()
        );

        if($jwtToken instanceof Token)
        {
            $token = $this->overrideUserAccessTokenToAppOne($token, $request->get('client_id', ''));
            $token = $this->appendIdToken($token, $jwtToken);
        }

        return new JsonResponse($token, Response::HTTP_OK);
    }

    private function overrideUserAccessTokenToAppOne(array $token, string $clientId): array
    {
        $query = 'select pac.id as client_id, acc.user_id as user_id from pim_api_client as pac left join akeneo_connectivity_connection as acc on (pac.id=acc.client_id) where pac.marketplace_public_app_id =:client_id';
        $result = $this->connection->fetchAssoc($query, [
            'client_id' => $clientId,
        ]);

        $userId = $result['user_id']; //Fake user created for this connection / connected app
        $clientId = $result['client_id']; //Id of the client in pim_api_client

        $query = 'select paat.token from akeneo_pim.pim_api_access_token as paat where paat.client =:client_id AND paat.user =:user_id ORDER BY paat.id DESC LIMIT 1';
        $result = $this->connection->fetchAssoc($query, [
            'user_id' => $userId,
            'client_id' => $clientId,
        ]);

        $token['access_token'] = $result['token'];

        return $token;
    }

    private function appendIdToken(array $token, Token $jwtToken): array
    {
        $token['id_token'] = $jwtToken->toString();

        return $token;
    }
}
