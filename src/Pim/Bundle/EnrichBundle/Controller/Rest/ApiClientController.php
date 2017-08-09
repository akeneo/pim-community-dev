<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use FOS\OAuthServerBundle\Entity\ClientManager;
use OAuth2\OAuth2;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * API client controller
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ApiClientController
{
    /** @var ClientManager */
    protected $clientManager;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /**
     * @param ClientManager       $clientManager
     * @param ValidatorInterface  $validator
     * @param NormalizerInterface $constraintViolationNormalizer
     */
    public function __construct(
        ClientManager $clientManager,
        ValidatorInterface $validator,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->clientManager = $clientManager;
        $this->validator = $validator;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_api_connection_manage")
     */
    public function createAction(Request $request)
    {
        $client = $this->clientManager->createClient();

        $data = json_decode($request->getContent(), true);

        if (isset($data['label'])) {
            $client->setLabel($data['label']);
        }
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);

        $violations = $this->validator->validate($client);
        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api'
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $this->clientManager->updateClient($client);

        return new JsonResponse();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_api_connection_manage")
     */
    public function revokeAction(Request $request, $publicId)
    {
        $clientManager = $this->clientManager;
        $client = $clientManager->findClientByPublicId($publicId);

        if (null === $client) {
            throw new NotFoundHttpException(
                sprintf('Client with public id %s does not exist.', $publicId)
            );
        }
        $clientManager->deleteClient($client);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
