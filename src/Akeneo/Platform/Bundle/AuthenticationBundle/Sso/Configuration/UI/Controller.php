<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\UI;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\CreateOrUpdateConfigurationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for SSO configuration.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Controller
{
    private const CONFIGURATION_CODE = 'authentication_sso';

    /** @var ValidatorInterface */
    private $validator;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var CreateOrUpdateConfigurationHandler */
    private $createOrUpdateConfigHandler;

    public function __construct(
        ValidatorInterface $validator,
        NormalizerInterface $normalizer,
        CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler
    ) {
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->createOrUpdateConfigHandler = $createOrUpdateConfigHandler;
    }

    public function saveAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $createOrUpdateConfig = new CreateOrUpdateConfiguration(
            self::CONFIGURATION_CODE,
            $data['enabled'] ?? false,
            $data['identityProviderEntityId'] ?? '',
            $data['identityProviderUrl'] ?? '',
            $data['identityProviderPublicCertificate'] ?? '',
            $data['serviceProviderEntityId'] ?? '',
            $data['serviceProviderPublicCertificate'] ?? '',
            $data['serviceProviderPrivateCertificate'] ?? ''
        );

        $errors = $this->validator->validate($createOrUpdateConfig);

        if (0 < $errors->count()) {
            $normalizedErrors = $this->normalizer->normalize(
                $errors,
                'internal_api'
            );

            return new JsonResponse($normalizedErrors, 400);
        }

        $this->createOrUpdateConfigHandler->handle($createOrUpdateConfig);

        return new JsonResponse();
    }
}
