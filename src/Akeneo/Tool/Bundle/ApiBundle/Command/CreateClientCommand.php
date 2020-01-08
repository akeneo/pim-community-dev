<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand as CreationCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Cli\CreateConnectionCommand;
use OAuth2\OAuth2;
use Symfony\Component\Console\Command\Command;
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
class CreateClientCommand extends Command
{
    protected static $defaultName = 'pim:oauth-server:create-client';

    /** @var CreateConnectionCommand */
    private $createConnection;

    public function __construct(CreateConnectionHandler $createConnection)
    {
        parent::__construct();

        $this->createConnection = $createConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
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
        $rawLabel = $input->getArgument('label');
        if (strlen($rawLabel) < 3) {
            $rawLabel = microtime(true);
        }
        $rawCode = $label = substr(trim($rawLabel), 0, 100);
        $code = preg_replace('#[^A-Za-z0-9_]#', '_', $rawCode);

        $command = new CreationCommand($code, $label, FlowType::OTHER);
        $connectionWithCredentials = $this->createConnection->handle($command);
        $output->writeln([
            'A new client has been added.',
            sprintf('client_id: <info>%s</info>', $connectionWithCredentials->clientId()),
            sprintf('secret: <info>%s</info>', $connectionWithCredentials->secret()),
        ]);

        $output->writeln(sprintf('label: <info>%s</info>', $connectionWithCredentials->label()));

        return 0;
    }
}
