<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Apps\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Apps\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Apps\Application\Settings\Command\RegenerateConnectionSecretHandler;
use Akeneo\Apps\Application\Settings\Query\FetchConnectionsHandler;
use Akeneo\Apps\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Settings\Model\ValueObject\FlowType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * TODO: To remove
 * @deprecated
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-connection:create';

    /** @var CreateConnectionHandler */
    private $createConnectionHandler;

    /** @var FetchConnectionsHandler */
    private $fetchConnectionsHandler;

    /** @var RegenerateConnectionSecretHandler */
    private $regenerateConnectionSecretHandler;

    public function __construct(
        CreateConnectionHandler $createConnectionHandler,
        FetchConnectionsHandler $fetchConnectionsHandler,
        RegenerateConnectionSecretHandler $regenerateConnectionSecretHandler
    ) {
        parent::__construct();

        $this->createConnectionHandler = $createConnectionHandler;
        $this->fetchConnectionsHandler = $fetchConnectionsHandler;
        $this->regenerateConnectionSecretHandler = $regenerateConnectionSecretHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $errorList = [];
        foreach ($this->connectionsToCreate() as $connection) {
            $errorList = array_merge(
                $errorList,
                $this->createConnection($connection['code'], $connection['label'], $connection['flowType'])
            );
        }

        $this->displayErrors($errorList);
        $connections = $this->fetchConnectionsHandler->query();
        $output->writeln(sprintf('<info>%s connections</info>', count($connections)));

//        $command = new RegenerateConnectionSecretCommand('AS_400');
//        $this->regenerateConnectionSecretHandler->handle($command);
    }

    private function createConnection(string $code, string $label, string $flowType): array
    {
        $errorList = [];
        try {
            $command = new CreateConnectionCommand($code, $label, $flowType);
            $this->createConnectionHandler->handle($command);
        } catch (ConstraintViolationListException $e) {
            $errorList = $this->buildViolationResponse($e->getConstraintViolationList());
        } catch (\Exception $e) {
            $errorList[] = ['name' => '', 'reason' => $e->getMessage()];
        }

        return $errorList;
    }

    private function displayErrors(array $errorList): void
    {
        if (empty($errorList)) {
            return;
        }
        var_dump($errorList);
    }

    private function connectionsToCreate(): array
    {
        return [
            [
                'code' => 'AS_400',
                'label' => 'AS 400',
                'flowType' => FlowType::DATA_SOURCE,
            ],
            [
                'code' => 'magento',
                'label' => 'Magento Connector',
                'flowType' => FlowType::DATA_DESTINATION,
            ],
            [
                'code' => 'Google_Shopping',
                'label' => 'Google Shopping',
                'flowType' => FlowType::DATA_DESTINATION,
            ],
            [
                'code' => 'Bynder',
                'label' => 'Bynder DAM',
                'flowType' => FlowType::OTHER,
            ],
        ];
    }

    private function buildViolationResponse(ConstraintViolationListInterface $constraintViolationList): array
    {
        $errors = [];
        foreach ($constraintViolationList as $constraintViolation) {
            $errors[] = [
                'name' => $constraintViolation->getPropertyPath(),
                'reason' => $constraintViolation->getMessage(),
            ];
        }

        return $errors;
    }
}
