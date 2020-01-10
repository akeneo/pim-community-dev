<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand as CreationCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectionCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-connection:create';

    /** @var CreateConnectionHandler */
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
            ->setDescription('Creates a new connection')
            ->addArgument(
                'code',
                InputArgument::REQUIRED,
                'Code of the connection.'
            )
            ->addOption(
                'flow-type',
                null,
                InputOption::VALUE_OPTIONAL,
                sprintf(
                    '%s | %s | %s',
                    FlowType::DATA_SOURCE,
                    FlowType::DATA_DESTINATION,
                    FlowType::OTHER
                ),
                FlowType::OTHER
            )
            ->addOption(
                'label',
                null,
                InputOption::VALUE_OPTIONAL,
                'Label of the connection. Default will be the provided code.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $label = $input->getOption('label') ?? $code;
        $flowType = $input->getOption('flow-type');

        try {
            $command = new CreationCommand($code, $label, $flowType);
            $connectionWithCredentials = $this->createConnection->handle($command);
            $output->writeln([
                '<info>A new connection has been created with the following settings:</info>',
                sprintf('Code: %s', $connectionWithCredentials->code()),
                sprintf('Client ID: %s', $connectionWithCredentials->clientId()),
                sprintf('Secret: %s', $connectionWithCredentials->secret()),
                sprintf('Username: %s', $connectionWithCredentials->username()),
                sprintf('Password: %s', $connectionWithCredentials->password()),
            ]);
        } catch (ConstraintViolationListException $exceptionList) {
            foreach ($exceptionList->getConstraintViolationList() as $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }

            return 1;
        }

        return 0;
    }
}
