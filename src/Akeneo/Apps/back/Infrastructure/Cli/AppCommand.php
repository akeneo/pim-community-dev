<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Command\RegenerateAppSecretCommand;
use Akeneo\Apps\Application\Command\RegenerateAppSecretHandler;
use Akeneo\Apps\Application\Query\FetchAppsHandler;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
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
class AppCommand extends Command
{
    protected static $defaultName = 'akeneo:app:create';

    /** @var CreateAppHandler */
    private $createAppHandler;
    /** @var FetchAppsHandler */
    private $fetchAppsHandler;
    /** @var RegenerateAppSecretHandler */
    private $regenerateAppSecretHandler;

    public function __construct(CreateAppHandler $createAppHandler, FetchAppsHandler $fetchAppsHandler, RegenerateAppSecretHandler $regenerateAppSecretHandler)
    {
        parent::__construct();

        $this->createAppHandler = $createAppHandler;
        $this->fetchAppsHandler = $fetchAppsHandler;
        $this->regenerateAppSecretHandler = $regenerateAppSecretHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $errorList = [];
        foreach ($this->appsToCreate() as $app) {
            $errorList = array_merge(
                $errorList,
                $this->createApp($app['code'], $app['label'], $app['flowType'])
            );
        }

        $this->displayErrors($errorList);
        $apps = $this->fetchAppsHandler->query();
        $output->writeln(sprintf('<info>%s apps</info>', count($apps)));

//        $command = new RegenerateAppSecretCommand('AS_400');
//        $this->regenerateAppSecretHandler->handle($command);
    }

    private function createApp(string $code, string $label, string $flowType): array
    {
        $errorList = [];
        try {
            $command = new CreateAppCommand($code, $label, $flowType);
            $this->createAppHandler->handle($command);
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

    private function appsToCreate(): array
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
