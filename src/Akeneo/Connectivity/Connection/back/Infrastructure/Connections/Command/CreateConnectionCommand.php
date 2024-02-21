<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand as CreationCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectionCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'akeneo:connectivity-connection:create';

    public function __construct(
        private CreateConnectionHandler $createConnection,
        private TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
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
                \sprintf(
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
            )
            ->addOption(
                'auditable',
                null,
                InputOption::VALUE_OPTIONAL,
                'If you want the connection to be auditable.',
                false
            )
            ->addOption(
                'user-group',
                null,
                InputOption::VALUE_OPTIONAL,
                'If you want the connection to be added to a user group.',
                null
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getArgument('code');
        $label = $input->getOption('label') ?? $code;
        $flowType = $input->getOption('flow-type');
        $auditable = /* --auditable */ null === $input->getOption('auditable')
            || /* --auditable=true|false */ \filter_var($input->getOption('auditable'), FILTER_VALIDATE_BOOLEAN);
        $userGroup = $input->getOption('user-group');

        try {
            $command = new CreationCommand($code, $label, $flowType, $auditable, null, $userGroup);
            $connectionWithCredentials = $this->createConnection->handle($command);
            $output->writeln([
                '<info>A new connection has been created with the following settings:</info>',
                \sprintf('Code: %s', $connectionWithCredentials->code()),
                \sprintf('Client ID: %s', $connectionWithCredentials->clientId()),
                \sprintf('Secret: %s', $connectionWithCredentials->secret()),
                \sprintf('Username: %s', $connectionWithCredentials->username()),
                \sprintf('Password: %s', $connectionWithCredentials->password()),
                \sprintf('Auditable: %s', $connectionWithCredentials->auditable() ? 'yes' : 'no'),
            ]);
        } catch (ConstraintViolationListException $exceptionList) {
            foreach ($exceptionList->getConstraintViolationList() as $e) {
                $output->writeln(\sprintf('<error>%s</error>', $this->translator->trans($e->getMessage(), [], 'jsmessages')));
            }

            return 1;
        }

        return 0;
    }
}
