<?php

namespace Pim\Bundle\ApiBundle\Command;

use OAuth2\OAuth2;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
            ->addOption(
                'label',
                null,
                InputOption::VALUE_REQUIRED,
                'Sets a label to ease the administration of client ids.'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'The output format (txt, json or xml)',
                'txt'
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

        if ($input->hasOption('label')) {
            $client->setLabel($input->getOption('label'));
        }

        $clientManager->updateClient($client);

        $this->displayOutput($output, $client, $input->getOption('format'));

        return 0;
    }


    /**
     * Returns the output into the selected format.
     * @param OutputInterface $output
     * @param ClientInterface $client
     * @param string $format
     */
    private function displayOutput(OutputInterface $output, ClientInterface $client, $format)
    {
        $serializer = $this->getContainer()->get('pim_serializer');
        $credentials = [
            'client_id' => $client->getPublicId(),
            'secret'    => $client->getSecret()
        ];


        if ($hasLabel = (null !== $client->getLabel())) {
            $credentials['label'] = addslashes($client->getLabel());
        }

        switch ($format) {
            case 'xml':
                $xmlContent = $serializer->encode(
                    $credentials,
                    'xml' ,
                    [
                        'xml_root_node_name' => 'credentials',
                        'xml_encoding' => 'UTF-8'
                    ]
                );

                $output->writeln($xmlContent);
                break;
            case 'json':
                $jsonContent = $serializer->encode($credentials, 'json');

                $output->writeln($jsonContent);
                break;
            default:
                $output->writeln([
                    'A new client has been added.',
                    sprintf('client_id: <info>%s</info>', $credentials['client_id']),
                    sprintf('secret: <info>%s</info>', $credentials['secret']),
                ]);

                if ($hasLabel) {
                    $output->writeln(sprintf('label: <info>%s</info>', $credentials['label']));
                }
        }
    }
}
