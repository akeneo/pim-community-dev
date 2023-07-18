<?php

namespace Akeneo\Platform\Installer\Infrastructure\Command;

use Akeneo\Platform\Requirements;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check requirements command
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckRequirementsCommand extends Command
{
    protected static $defaultName = 'pim:installer:check-requirements';
    protected static $defaultDescription = 'heck requirements for Akeneo PIM';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Akeneo PIM requirements check:</info>');

        $this->renderRequirements(
            $input,
            $output,
            new Requirements()
        );

        return Command::SUCCESS;
    }

    /**
     * @throws \RuntimeException
     */
    protected function renderRequirements(
        InputInterface $input,
        OutputInterface $output,
        Requirements $collection
    ): void {
        $this->renderTable($collection->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getPhpIniRequirements(), 'PHP requirements', $output);
        $this->renderTable($collection->getPimRequirements(), 'Pim requirements', $output);
        $this->renderTable($collection->getRecommendations(), 'Recommendations', $output);

        if (count($collection->getFailedRequirements())) {
            $this->renderTable($collection->getFailedRequirements(), 'Errors', $output);

            throw new \RuntimeException(
                'Some system requirements are not fulfilled. Please check output messages and fix them'
            );
        }
    }

    protected function renderTable(array $collection, string $header, OutputInterface $output): void
    {
        $table = new Table($output);

        $table
            ->setHeaders(['Check  ', $header])
            ->setRows([]);

        foreach ($collection as $requirement) {
            if ($requirement->isFulfilled()) {
                $table->addRow(['OK', $requirement->getTestMessage()]);
            } else {
                $table->addRow(
                    [
                        $requirement->isOptional() ? 'WARNING' : 'ERROR',
                        $requirement->getHelpText(),
                    ]
                );
            }
        }

        $table->render();
    }
}
