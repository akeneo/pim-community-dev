<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\UI;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\CreateArchive;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\CreateOrUpdateConfigurationHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProviderDefaultConfiguration;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /** @var Repository */
    private $repository;

    /** @var ServiceProviderDefaultConfiguration */
    private $serviceProviderDefaultConfiguration;

    /** @var CreateArchive */
    private $createArchive;

    public function __construct(
        ValidatorInterface $validator,
        NormalizerInterface $normalizer,
        CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler,
        Repository $repository,
        ServiceProviderDefaultConfiguration $serviceProviderDefaultConfiguration,
        CreateArchive $createArchive
    ) {
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->createOrUpdateConfigHandler = $createOrUpdateConfigHandler;
        $this->repository = $repository;
        $this->serviceProviderDefaultConfiguration = $serviceProviderDefaultConfiguration;
        $this->createArchive = $createArchive;
    }

    public function saveAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        $createOrUpdateConfig = new CreateOrUpdateConfiguration(
            self::CONFIGURATION_CODE,
            $data['is_enabled'] ?? false,
            $data['identity_provider_entity_id'] ?? '',
            $data['identity_provider_sign_on_url'] ?? '',
            $data['identity_provider_logout_url'] ?? '',
            $data['identity_provider_public_certificate'] ?? '',
            $data['service_provider_entity_id'] ?? '',
            $data['service_provider_public_certificate'] ?? '',
            $data['service_provider_private_certificate'] ?? ''
        );

        $errors = $this->validator->validate($createOrUpdateConfig);

        if (0 < $errors->count()) {
            $normalizedErrors = $this->normalizer->normalize(
                $errors,
                'internal_api'
            );

            return new JsonResponse($normalizedErrors, Response::HTTP_BAD_REQUEST);
        }

        $this->createOrUpdateConfigHandler->handle($createOrUpdateConfig);

        return new JsonResponse();
    }

    public function getAction(): JsonResponse
    {
        try {
            $config = $this->repository->find(self::CONFIGURATION_CODE);
            $normalizedConfig = $this->normalizer->normalize($config, 'internal_api');

            return new JsonResponse($normalizedConfig);
        } catch (ConfigurationNotFound $e) {

            $serviceProvider = $this->serviceProviderDefaultConfiguration->getServiceProvider()->toArray();

            return new JsonResponse([
                'is_enabled'                           => false,
                'identity_provider_entity_id'          => '',
                'identity_provider_sign_on_url'        => '',
                'identity_provider_logout_url'         => '',
                'identity_provider_public_certificate' => '',
                'service_provider_entity_id'           => $serviceProvider['entityId'],
                'service_provider_public_certificate'  => $serviceProvider['publicCertificate'],
                'service_provider_private_certificate' => $serviceProvider['privateCertificate'],
            ]);
        }
    }

    public function downloadAuthenticationLogsAction()
    {
        try
        {
            return new BinaryFileResponse(
                $this->createArchive->create(),
                Response::HTTP_OK,
                [
                    'Content-Disposition' => sprintf('attachment; filename="%s"', 'authenticationLogs' . date('YmdHis') . '.zip'),
                    'Content-type' => 'application/zip',
                ]
            );

        } catch (\Exception $e)
        {
            throw new NotFoundHttpException("Unable to find archive file");
        }
    }
}
