<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Audit\Service\UpdateProductEventCountService;
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
    protected static $defaultName = 'akeneo:apps-audit:update-data';

    /** @var UpdateProductEventCountService */
    private $updateProductEventCountService;

    public function __construct(UpdateProductEventCountService $updateProductEventCountService)
    {
        parent::__construct();
        $this->updateProductEventCountService = $updateProductEventCountService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $datetime->setTime(0, 0, 0, 0);
        $this->updateProductEventCountService->execute($datetime);
    }
}
