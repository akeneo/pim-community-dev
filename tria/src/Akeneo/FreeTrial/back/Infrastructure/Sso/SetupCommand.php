<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Sso;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfigurationHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SetupCommand extends Command
{
    private CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler;
    private ValidatorInterface $validator;
    private string $samlIdpEntityId;
    private string $samlIdpLoginUrl;
    private string $samlIdpLogoutUrl;
    private string $samlIdpCertificate;
    private string $samlSpEntityId;
    private string $samlSpCertificateBase64;
    private string $samlSpPrivateKeyBase64;

    public function __construct(
        CreateOrUpdateConfigurationHandler  $createOrUpdateConfigHandler,
        ValidatorInterface $validator,
        string $samlIdpEntityId,
        string $samlIdpLoginUrl,
        string $samlIdpLogoutUrl,
        string $samlIdpCertificate,
        string $samlSpEntityId,
        string $samlSpCertificateBase64,
        string $samlSpPrivateKeyBase64
    ) {
        parent::__construct();

        $this->createOrUpdateConfigHandler = $createOrUpdateConfigHandler;
        $this->validator = $validator;
        $this->samlIdpEntityId = $samlIdpEntityId;
        $this->samlIdpLoginUrl = $samlIdpLoginUrl;
        $this->samlIdpLogoutUrl = $samlIdpLogoutUrl;
        $this->samlIdpCertificate = $samlIdpCertificate;
        $this->samlSpEntityId = $samlSpEntityId;
        $this->samlSpCertificateBase64 = $samlSpCertificateBase64;
        $this->samlSpPrivateKeyBase64 = $samlSpPrivateKeyBase64;
    }

    protected function configure()
    {
        $this->setName('akeneo:free-trial:setup-sso')
            ->setDescription('Set up the SSO authentication for the Free Trial')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $samlSpCertificate = base64_decode($this->samlSpCertificateBase64, true);
        $samlSpPrivateKey = base64_decode($this->samlSpPrivateKeyBase64, true);

        if ($samlSpCertificate === false || $samlSpPrivateKey === false) {
            $io->error([
                'The SSO authentication has not been set up.',
                'Please check the AKENEO_CONNECT_SAML_SP_CERTIFICATE_BASE64 and AKENEO_CONNECT_SAML_SP_PRIVATE_KEY_BASE64 environment variables used for the SAML Service Provider configuration',
            ]);

            return 1;
        }

        $ssoConfiguration = new CreateOrUpdateConfiguration(
            Configuration::DEFAULT_CODE,
            true,
            $this->samlIdpEntityId,
            $this->samlIdpLoginUrl,
            $this->samlIdpLogoutUrl,
            $this->samlIdpCertificate,
            $this->samlSpEntityId,
            $samlSpCertificate,
            $samlSpPrivateKey
        );

        $violations = $this->validator->validate($ssoConfiguration);

        if ($violations->count() > 0) {
            $errors = array_map(fn ($violation) => $this->formatViolation($violation), iterator_to_array($violations));
            $io->error(array_merge(['The SSO authentication has not been set up.'], $errors));

            return 1;
        }

        $this->createOrUpdateConfigHandler->handle($ssoConfiguration);

        $io->success('The SSO authentication is set up.');

        return 0;
    }

    private function formatViolation(ConstraintViolationInterface $violation): string
    {
        switch ($violation->getPropertyPath()) {
            case 'identityProviderEntityId':
                $invalidArgument = 'Entity ID';
                break;
            case 'identityProviderSignOnUrl':
                $invalidArgument = 'Login URL';
                break;
            case 'identityProviderLogoutUrl':
                $invalidArgument = 'Logout URL';
                break;
            case 'identityProviderCertificate':
                $invalidArgument = 'Certificate';
                break;
            case 'serviceProviderEntityId':
                $invalidArgument = 'Service provider entity id';
                break;
            case 'serviceProviderCertificate':
                $invalidArgument = 'Service provider certificate';
                break;
            case 'serviceProviderPrivateKey':
                $invalidArgument = 'Service provider private key';
                break;
            default:
                $invalidArgument = $violation->getPropertyPath();
        }

        return sprintf('%s: %s', $invalidArgument, $violation->getMessage());
    }
}
