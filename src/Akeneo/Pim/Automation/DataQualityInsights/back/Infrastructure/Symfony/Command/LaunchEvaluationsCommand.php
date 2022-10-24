<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\EvaluationsLauncher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LaunchEvaluationsCommand extends Command
{
    protected static $defaultName = 'pim:data-quality-insights:evaluations';
    protected static $defaultDescription = 'Launch the evaluations of products and structure';

    public function __construct(private EvaluationsLauncher $evaluationsLauncher)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->evaluationsLauncher->run();

        return Command::SUCCESS;
    }
}
