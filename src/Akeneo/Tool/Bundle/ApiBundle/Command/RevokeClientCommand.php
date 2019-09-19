<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Command;

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Component\Console\Command\Command;
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
class RevokeClientCommand extends Command
{
    protected static $defaultName = 'pim:oauth-server:revoke-client';

    /** @var ClientManagerInterface */
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
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
        $client = $this->clientManager->findClientByPublicId($input->getArgument('client_id'));

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
        $this->clientManager->deleteClient($client);

        $output->writeln(sprintf(
            'Client with public id <info>%s</info> and secret <info>%s</info> has been revoked.',
            $clientId,
            $secret
        ));

        return 0;
    }
}
