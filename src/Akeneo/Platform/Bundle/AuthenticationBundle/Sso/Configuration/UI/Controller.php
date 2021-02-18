<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\UI;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\CertificateMetadata;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\CreateArchive;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfigurationHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\CertificateExpirationDate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProviderDefaultConfiguration;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
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

    /** @var PresenterInterface */
    private $datePresenter;

    /** @var UserContext */
    private $userContext;

    /** @var string */
    private $akeneoPimUrl;

    public function __construct(
        ValidatorInterface $validator,
        NormalizerInterface $normalizer,
        CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler,
        Repository $repository,
        ServiceProviderDefaultConfiguration $serviceProviderDefaultConfiguration,
        CreateArchive $createArchive,
        PresenterInterface $datePresenter,
        UserContext $userContext,
        string $akeneoPimUrl
    ) {
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->createOrUpdateConfigHandler = $createOrUpdateConfigHandler;
        $this->repository = $repository;
        $this->serviceProviderDefaultConfiguration = $serviceProviderDefaultConfiguration;
        $this->createArchive = $createArchive;
        $this->datePresenter = $datePresenter;
        $this->userContext = $userContext;
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
            Configuration::DEFAULT_CODE,
            $data['configuration']['is_enabled'] ?? false,
            $data['configuration']['identity_provider_entity_id'] ?? '',
            $data['configuration']['identity_provider_sign_on_url'] ?? '',
            $data['configuration']['identity_provider_logout_url'] ?? '',
            $data['configuration']['identity_provider_certificate'] ?? '',
            $data['configuration']['service_provider_entity_id'] ?? '',
            $data['configuration']['service_provider_certificate'] ?? '',
            $data['configuration']['service_provider_private_key'] ?? ''
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
            'service_provider_acs_url'      => sprintf('%s/saml/acs', $this->akeneoPimUrl),
        ];

        try {
            $config = $this->repository->find(Configuration::DEFAULT_CODE);
            $configArray = $config->toArray();
            $expirationDate = (new CertificateMetadata($configArray['serviceProvider']['certificate']))->getExpirationDate();

            return new JsonResponse([
                'configuration' => [
                    'is_enabled'                    => $configArray['isEnabled'],
                    'identity_provider_entity_id'   => $configArray['identityProvider']['entityId'],
                    'identity_provider_sign_on_url' => $configArray['identityProvider']['signOnUrl'],
                    'identity_provider_logout_url'  => $configArray['identityProvider']['logoutUrl'],
                    'identity_provider_certificate' => $configArray['identityProvider']['certificate'],
                    'service_provider_entity_id'    => $configArray['serviceProvider']['entityId'],
                    'service_provider_certificate'  => $configArray['serviceProvider']['certificate'],
                    'service_provider_private_key'  => $configArray['serviceProvider']['privateKey'],
                ],
                'meta' => [
                    'service_provider_certificate_expiration_date' => $expirationDate ? $this->formatDate($expirationDate) : null,
                    'service_provider_certificate_expires_soon'    => $expirationDate ?
                        $expirationDate->doesExpireInLessThanDays(
                            new \DateTimeImmutable('now'),
                            Certificate::EXPIRATION_WARNING_IN_DAYS
                        ) : null,
                ] + $staticConfiguration
            ]);
        } catch (ConfigurationNotFound $e) {
            $serviceProvider = $this->serviceProviderDefaultConfiguration->getServiceProvider()->toArray();
            $expirationDate = (new CertificateMetadata($serviceProvider['certificate']))->getExpirationDate();

            return new JsonResponse([
                'configuration' => [
                    'is_enabled'                    => false,
                    'identity_provider_entity_id'   => '',
                    'identity_provider_sign_on_url' => '',
                    'identity_provider_logout_url'  => '',
                    'identity_provider_certificate' => '',
                    'service_provider_entity_id'    => $serviceProvider['entityId'],
                    'service_provider_certificate'  => $serviceProvider['certificate'],
                    'service_provider_private_key'  => $serviceProvider['privateKey'],
                ],
                'meta' => [
                    'service_provider_certificate_expiration_date' => $expirationDate ? $this->formatDate($expirationDate) : null,
                    'service_provider_certificate_expires_soon'    => $expirationDate ?
                        $expirationDate->doesExpireInLessThanDays(
                            new \DateTimeImmutable('now'),
                            Certificate::EXPIRATION_WARNING_IN_DAYS
                        ) : null,
                ] + $staticConfiguration
            ]);
        }
    }

    /*
     * @AclAncestor("pimee_sso_configuration")
     */
    public function downloadAuthenticationLogsAction()
    {
        try {
            return (new BinaryFileResponse(
                $this->createArchive->create(),
                Response::HTTP_OK,
                [
                    'Content-Disposition' => sprintf('attachment; filename="authenticationLogs%s.zip"', date('YmdHis')),
                    'Content-Type'        => 'application/zip',
                ]
            ))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            throw new NotFoundHttpException("Unable to find archive file");
        }
    }

    private function formatDate(CertificateExpirationDate $date)
    {
        return $this->datePresenter->present(
            $date->toDateTime(),
            [
                'locale'   => $this->userContext->getUiLocaleCode(),
                'timezone' => $this->userContext->getUserTimezone(),
            ]
        );
    }
}
