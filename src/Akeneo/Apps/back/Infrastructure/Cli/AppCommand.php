<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Query\FetchAppsHandler;
use Akeneo\Apps\Application\Query\FetchAppsQuery;
use Akeneo\Apps\Domain\Model\AppCode;
use Akeneo\Apps\Domain\Model\FlowType;
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
        $this->createAppHandler->handle(new CreateAppCommand(
            AppCode::create('AS_400'),
            'AS 400',
            FlowType::create(FlowType::DATA_SOURCE)
        ));

        $this->createAppHandler->handle(new CreateAppCommand(
            AppCode::create('MagentoConnector'),
            'Magento Connector',
            FlowType::create(FlowType::DATA_DESTINATION)
        ));

        $this->createAppHandler->handle(new CreateAppCommand(
            AppCode::create('Google_Shopping'),
            'Google Shopping',
            FlowType::create(FlowType::DATA_DESTINATION)
        ));

        $this->createAppHandler->handle(new CreateAppCommand(
            AppCode::create('Bynder'),
            'Bynder DAM',
            FlowType::create(FlowType::OTHERS)
        ));

        $apps = $this->fetchAppsHandler->query(new FetchAppsQuery());
        $output->writeln(sprintf('<info>%s apps</info>', count($apps)));
    }
}
