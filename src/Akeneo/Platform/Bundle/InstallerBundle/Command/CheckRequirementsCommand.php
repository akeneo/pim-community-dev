<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Akeneo\Platform\Bundle\InstallerBundle\PimDirectoriesRegistry;
use Akeneo\Platform\CommunityVersion;
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

    /** @var PimDirectoriesRegistry */
    private $pimDirectoriesRegistry;

    /** @var string */
    private $pimVersion;

    /** @var string */
    private $env;

    public function __construct(
        PimDirectoriesRegistry $pimDirectoriesRegistry,
        string $pimVersion,
        string $env
    ) {
        parent::__construct();

        $this->pimDirectoriesRegistry = $pimDirectoriesRegistry;
        $this->pimVersion = $pimVersion;
        $this->env = $env;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Check requirements for Akeneo PIM');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Akeneo PIM requirements check:</info>');

        $this->renderRequirements($input, $output, $this->getRequirements());
    }

    protected function getRequirements(): Requirements
    {
        $baseDirectory = __DIR__.'/../../../';

        if (CommunityVersion::class !== $this->pimVersion) {
            $baseDirectory = $baseDirectory.'../../../';
        }

        $directories = [];
        if ($this->env !== 'behat') {
            $directories = $this->pimDirectoriesRegistry->getDirectories();
        }

        return new Requirements($baseDirectory, $directories);
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

    /**
     * Render requirements table
     *
     * @param array           $collection
     * @param string          $header
     * @param OutputInterface $output
     */
    protected function renderTable(array $collection, $header, OutputInterface $output)
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
                        $requirement->getHelpText()
                    ]
                );
            }
        }

        $table->render();
    }
}
