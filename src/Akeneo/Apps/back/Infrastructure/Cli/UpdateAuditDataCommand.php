<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Audit\Command\UpdateProductEventCountCommand;
use Akeneo\Apps\Application\Audit\Command\UpdateProductEventCountHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateAuditDataCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-audit:update-data';

    /** @var UpdateProductEventCountHandler */
    private $updateProductEventCountHandler;

    public function __construct(UpdateProductEventCountHandler $updateProductEventCountHandler)
    {
        parent::__construct();
        $this->updateProductEventCountHandler = $updateProductEventCountHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        // TODO: To calculate from refresh date
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $datetime->setTime(0, 0, 0, 0);

        $command = new UpdateProductEventCountCommand($datetime->format('Y-m-d'));
        $this->updateProductEventCountHandler->handle($command);
    }
}
