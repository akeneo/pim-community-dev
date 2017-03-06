<?php

namespace Pim\Bundle\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * This command revokes a pair of client id / secret.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RevokeClientCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:oauth-server:revoke-client')
            ->setDescription('This command revokes a pair of client id / secret')
            ->addArgument(
                'client_id',
                InputArgument::REQUIRED,
                'The client id to revoke.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->findClientByPublicId($input->getArgument('client_id'));

        if (null === $client) {
            $output->writeln('<error>No client found for this id.</error>');

            return -1;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            '<question>This operation is irreversible. Are you sure you want to revoke this client? (Y/n)</question>'
        );

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Client revocation cancelled.');

            return 0;
        }

        $clientId = $client->getPublicId();
        $secret = $client->getSecret();
        $clientManager->deleteClient($client);

        $output->writeln(sprintf(
            'Client with public id <info>%s</info> and secret <info>%s</info> has been revoked.',
            $clientId,
            $secret
        ));

        return 0;
    }
}
