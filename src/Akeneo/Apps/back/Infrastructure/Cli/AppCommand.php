<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Query\FetchAppsHandler;
use Akeneo\Apps\Application\Query\FetchAppsQuery;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\ValueObject\AppCode;
use Akeneo\Apps\Domain\Model\ValueObject\AppLabel;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'akeneo:app:create';

    private $createAppHandler;
    private $fetchAppsHandler;

    public function __construct(CreateAppHandler $createAppHandler, FetchAppsHandler $fetchAppsHandler)
    {
        parent::__construct();

        $this->createAppHandler = $createAppHandler;
        $this->fetchAppsHandler = $fetchAppsHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $command = new CreateAppCommand('AS_400', 'AS 400', FlowType::DATA_SOURCE);
        $this->createAppHandler->handle($command);

        $command = new CreateAppCommand('MagentoConnector', 'Magento Connector', FlowType::DATA_DESTINATION);
        $this->createAppHandler->handle($command);

        $command = new CreateAppCommand('Google_Shopping', 'Google Shopping', FlowType::DATA_DESTINATION);
        $this->createAppHandler->handle($command);

        $command = new CreateAppCommand('Bynder', 'Bynder DAM', FlowType::OTHER);
        $this->createAppHandler->handle($command);

        $apps = $this->fetchAppsHandler->query();
        $output->writeln(sprintf('<info>%s apps</info>', count($apps)));
    }
}
