<?php

namespace Pim\Bundle\ApiBundle\Command;

use OAuth2\OAuth2;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command creates a new pair of client id / secret for the web API.
 *
 * Heavily inspired by https://github.com/Sylius/Sylius/blob/v1.0.0-beta.1/src/Sylius/Bundle/ApiBundle/Command/CreateClientCommand.php
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateClientCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:oauth-server:create-client')
            ->setDescription('Creates a new pair of client id / secret for the web API')
            ->addOption(
                'redirect_uri',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets redirect uri for client.'
            )
            ->addOption(
                'grant_type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed grant type for client.',
                [OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN]
            )
            ->addArgument(
                'label',
                InputArgument::REQUIRED,
                'Sets a label to ease the administration of client ids.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();

        $client->setRedirectUris($input->getOption('redirect_uri'));
        $client->setAllowedGrantTypes($input->getOption('grant_type'));
        $client->setLabel($input->getArgument('label'));

        $clientManager->updateClient($client);

        $output->writeln([
            'A new client has been added.',
            sprintf('client_id: <info>%s</info>', $client->getPublicId()),
            sprintf('secret: <info>%s</info>', $client->getSecret()),
        ]);

        $label = $client->getLabel();
        if (null !== $label) {
            $output->writeln(sprintf('label: <info>%s</info>', $label));
        }

        return 0;
    }
}
