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

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfigurationHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProviderDefaultConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ActivateCommand extends Command
{
    private CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler;

    private ValidatorInterface $validator;

    private ServiceProviderDefaultConfiguration $serviceProviderDefaultConfiguration;

    public function __construct(
        CreateOrUpdateConfigurationHandler $createOrUpdateConfigHandler,
        ValidatorInterface $validator,
        ServiceProviderDefaultConfiguration $serviceProviderDefaultConfiguration
    ) {
        parent::__construct();

        $this->createOrUpdateConfigHandler = $createOrUpdateConfigHandler;
        $this->validator = $validator;
        $this->serviceProviderDefaultConfiguration = $serviceProviderDefaultConfiguration;
    }

    protected function configure()
    {
        $this->setName('akeneo:sso:activate')
            ->setDescription('Activate the SSO authentication for the PIM')
            ->addArgument('entityId', InputArgument::REQUIRED, 'Entity ID')
            ->addArgument('loginUrl', InputArgument::REQUIRED, 'Login URL')
            ->addArgument('logoutUrl', InputArgument::REQUIRED, 'Logout URL')
            ->addArgument('certificate', InputArgument::REQUIRED, 'Certificate')
            ->addArgument('spEntityId', InputArgument::OPTIONAL, 'Service provider entity id')
            ->addArgument('spCertificate', InputArgument::OPTIONAL, 'Service provider certificate')
            ->addArgument('spPrivateKey', InputArgument::OPTIONAL, 'Service provider private key')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $serviceProvider = $this->serviceProviderDefaultConfiguration->getServiceProvider()->toArray();

        $ssoConfiguration = new CreateOrUpdateConfiguration(
            Configuration::DEFAULT_CODE,
            true,
            $input->getArgument('entityId'),
            $input->getArgument('loginUrl'),
            $input->getArgument('logoutUrl'),
            $input->getArgument('certificate'),
            $input->getArgument('spEntityId') ?? $serviceProvider['spEntityId'],
            $input->getArgument('spCertificate') ?? $serviceProvider['certificate'],
            $input->getArgument('spPrivateKey') ?? $serviceProvider['privateKey']
        );

        $violations = $this->validator->validate($ssoConfiguration);

        if ($violations->count() > 0) {
            $errors = array_map(fn ($violation) => $this->formatViolation($violation), iterator_to_array($violations));
            $io->error(array_merge(['The SSO authentication has not been activated.'], $errors));

            return 1;
        }

        $this->createOrUpdateConfigHandler->handle($ssoConfiguration);

        $io->success('The SSO authentication is activated.');

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
