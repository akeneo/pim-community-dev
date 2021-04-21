<?php
declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Command;

use Jfcherng\Diff\DiffHelper;
use Jfcherng\Diff\Renderer\RendererConstant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseSchemaDiffCommand extends Command
{
    private const DB_REFERENCE_FILE = __DIR__ . '/../Resources/reference.pimdbschema.txt';

    /** @see https://github.com/jfcherng/php-diff */
    private const DIFF_MODE = 'Unified';

    protected static $defaultName = 'pimee:database:diff';

    protected function configure()
    {
        $this
            ->setDescription("This command outputs the differences between the given database schema file and a the reference for this branch.")
            ->addArgument('filename', InputArgument::OPTIONAL, "The filename of the database structure export.", IntrospectDatabaseCommand::DEFAULT_FILENAME)
            ->addOption('color', 'c', InputOption::VALUE_NONE, "Use color in output.", null)
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');
        $this->checkFile($filename);
        $differOptions = [
            // show how many neighbor lines
            'context' => 0,
            // ignore whitespace difference
            'ignoreWhitespace' => true,
        ];
        $rendererOptions = [
            'cliColorization' => $input->getOption('color') ? RendererConstant::CLI_COLOR_ENABLE : RendererConstant::CLI_COLOR_DISABLE,
        ];

        $lines = DiffHelper::calculateFiles(self::DB_REFERENCE_FILE, $filename, self::DIFF_MODE, $differOptions, $rendererOptions);
        $output->writeln($lines);

        return strlen($lines) === 0 ? 0 : -1;
    }

    private function checkFile(string $filename): void
    {
        if (file_exists($filename) && is_readable($filename)) {
            return;
        }

        throw new \InvalidArgumentException(sprintf("Cannot read from file '%s'. The file may be missing or there may be a permission problem.", $filename));
    }
}
