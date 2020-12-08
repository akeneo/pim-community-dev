<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\SwitchClientIndexer;
use phpDocumentor\Reflection\Types\Iterable_;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateMappingIndexForSaasCommand extends Command
{
    protected static $defaultName = 'akeneo:elasticsearch:update-mapping-for-saas';
    private string $projectDir;
    private array $switchClientIndexers;

    public function __construct(string $projectDir, iterable $switchClientIndexers)
    {
        parent::__construct(self::$defaultName);

        $this->projectDir = $projectDir;

        $this->switchClientIndexers = [];
        foreach ($switchClientIndexers as $switchClientIndexer) {
            Assert::isInstanceOf($switchClientIndexer, SwitchClientIndexer::class);
            $this->switchClientIndexers[] = $switchClientIndexer;
        }
    }

    public function configure()
    {
        $this
            ->addArgument(
                'entity_name',
                InputArgument::REQUIRED,
                'Entity name impacted by the update. Should be one of: "assets"'
            )
            ->setDescription('Reindex all entities using a new index.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if a switch client indexer is active.
        // It's a security to ensure the service and the tag is present.
        // Maybe it can be removed with another use case.
        Assert::notEmpty($this->switchClientIndexers, 'No switch client indexer are found.');

        $entityName = $input->getArgument('entity_name');

        $io = new SymfonyStyle($input, $output);
        if (!$io->confirm('Are you sure to continue?', true)) {
            $output->writeln("<info>You decided to abort your Elasticearch mapping update</info>");

            return;
        }

        // Use only new index name
        array_walk(
            $this->switchClientIndexers,
            fn (SwitchClientIndexer $switchClientIndexer) => $switchClientIndexer->setMode(
                SwitchClientIndexer::ONLY_NEXT_INDEX_MODE
            )
        );

        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        $arguments = array_merge(
            [$pathFinder->find(), $console],
            $this->getIndexJobArguments($entityName)
        );
        $process = new Process($arguments);

        $process->setTimeout(null);

        $output->writeln(sprintf('Command line: "%s"', $process->getCommandLine()));

        $process->start();
        $output->writeln(sprintf('<info>Start reindexation of %s...</info>', $entityName));
        while ($process->isRunning()) {
            sleep(2);
        }

        $output->writeln('<info>Done</info>');

        return 0;
    }

    private function getIndexJobArguments(string $entityName): array
    {
        switch ($entityName) {
            case 'assets':
                return ['akeneo:asset-manager:index-assets', '--all', '--no-interaction'];
            default:
                throw new \InvalidArgumentException(sprintf(
                    'This command is not implemented yet for \'%s\' entity.',
                    $entityName
                ));
        }
    }
}
