<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\UI;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\CertificateMetadata;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\CreateArchive;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfigurationHandler;
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

    /** @var string */
    private $akeneoPimUrl;

    public function __construct(
        ValidatorInterface $validator,
        NormalizerInterface $normalizer,
        CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler,
        Repository $repository,
        ServiceProviderDefaultConfiguration $serviceProviderDefaultConfiguration,
        CreateArchive $createArchive,
        string $akeneoPimUrl
    ) {
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->createOrUpdateConfigHandler = $createOrUpdateConfigHandler;
        $this->repository = $repository;
        $this->serviceProviderDefaultConfiguration = $serviceProviderDefaultConfiguration;
        $this->createArchive = $createArchive;
        $this->akeneoPimUrl = $akeneoPimUrl;
    }

    /*
     * @AclAncestor("pimee_sso_configuration")
     */
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
            $data['identity_provider_certificate'] ?? '',
            $data['service_provider_entity_id'] ?? '',
            $data['service_provider_certificate'] ?? '',
            $data['service_provider_private_key'] ?? ''
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

    /*
     * @AclAncestor("pimee_sso_configuration")
     */
    public function getAction(): JsonResponse
    {
        $staticConfiguration = [
            'service_provider_metadata_url' => sprintf('%s/saml/metadata', $this->akeneoPimUrl),
            'service_provider_acs_url' => sprintf('%s/saml/acs', $this->akeneoPimUrl),
        ];

        try {
            $config = $this->repository->find(self::CONFIGURATION_CODE);
            $configArray = $config->toArray();

            return new JsonResponse([
                'is_enabled'                    => $configArray['isEnabled'],
                'identity_provider_entity_id'   => $configArray['identityProvider']['entityId'],
                'identity_provider_sign_on_url' => $configArray['identityProvider']['signOnUrl'],
                'identity_provider_logout_url'  => $configArray['identityProvider']['logoutUrl'],
                'identity_provider_certificate' => $configArray['identityProvider']['certificate'],
                'service_provider_entity_id'    => $configArray['serviceProvider']['entityId'],
                'service_provider_certificate'  => $configArray['serviceProvider']['certificate'],
                'service_provider_private_key'  => $configArray['serviceProvider']['privateKey'],
                'service_provider_certificate_end_date' => (new CertificateMetadata($configArray['certificate']))->getEndDate(),
            ] + $staticConfiguration);
        } catch (ConfigurationNotFound $e) {
            $serviceProvider = $this->serviceProviderDefaultConfiguration->getServiceProvider()->toArray();

            return new JsonResponse([
                'is_enabled'                    => false,
                'identity_provider_entity_id'   => '',
                'identity_provider_sign_on_url' => '',
                'identity_provider_logout_url'  => '',
                'identity_provider_certificate' => '',
                'service_provider_entity_id'    => $serviceProvider['entityId'],
                'service_provider_certificate'  => $serviceProvider['certificate'],
                'service_provider_private_key'  => $serviceProvider['privateKey'],
                'service_provider_certificate_end_date' => (new CertificateMetadata($serviceProvider['certificate']))->getEndDate(),
            ] + $staticConfiguration);
        }
    }

    /*
     * @AclAncestor("pimee_sso_configuration")
     */
    public function downloadAuthenticationLogsAction()
    {
        try {
            return new BinaryFileResponse(
                $this->createArchive->create(),
                Response::HTTP_OK,
                [
                    'Content-Disposition' => sprintf('attachment; filename="%s"', 'authenticationLogs' . date('YmdHis') . '.zip'),
                    'Content-type' => 'application/zip',
                ]
            );
        } catch (\Exception $e) {
            throw new NotFoundHttpException("Unable to find archive file");
        }
    }
}
