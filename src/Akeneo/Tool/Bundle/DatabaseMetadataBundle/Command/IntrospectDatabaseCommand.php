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

use Akeneo\Tool\Component\DatabaseMetadata\DatabaseInspector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class IntrospectDatabaseCommand extends Command
{
    public const DEFAULT_FILENAME = 'akeneo_pim_db.metadata.txt';

    protected static $defaultName = 'pimee:database:inspect';

    /** @var DatabaseInspector  */
    private $inspector;

    public function __construct(DatabaseInspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function configure()
    {
        $this
         ->setDescription("Output the PIM database informations.")
         ->addUsage('Dump database structure and data informations.')
         ->setHelp('This command is used to either compare the PIM database structure with a reference or to troubleshoot problems.')
         ->addOption('db-name', 'd', InputOption::VALUE_REQUIRED, 'The database name when different from "akeneo_pim".', 'akeneo_pim')
         ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, sprintf('When present, this option makes the tool to save the output in a file. If the name of the file is not provided, it will default to "%s".', self::DEFAULT_FILENAME), false)
         ->addOption('jobs', 'j', InputOption::VALUE_NONE, 'Dump jobs from database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbName = $input->getOption('db-name');

        if ($input->getOption('file') !== false) {
            $filesystem = new Filesystem();

            if (null === $input->getOption('file')) {
                $filename = self::DEFAULT_FILENAME;
            } else {
                $filename = $input->getOption('file');
            }

            if (file_exists($filename)) {
                $filesystem->remove($filename);
            }
            $filesystem->touch($filename);
        } else {
            $filesystem = null;
            $filename = null;
        }

        $jobs = $input->getOption('jobs') === true ?: false;

        $outputContent = function (string $line) use ($output, $filesystem, $filename) {
            if (isset($filesystem) && $filesystem !== null) {
                $filesystem->appendToFile($filename, $line);
            } else {
                $output->write($line);
            }
        };

        foreach ($this->inspector->getTableList($dbName) as $row) {
            $line = sprintf("%s | %s\n", $row['table_name'], $row['table_type']);
            $outputContent($line);
        }

        foreach ($this->inspector->getColumnInfo($dbName) as $row) {
            $line = \trim(sprintf(
                "%s | %s | %s | %s | %s",
                $row['table_name'],
                $row['column_name'],
                $row['is_nullable'],
                $row['column_type'],
                $row['column_key']
            )) . PHP_EOL;
            $outputContent($line);
        }

        foreach ($this->inspector->getIndexes($dbName) as $row) {
            $line = \sprintf(
                "INDEX | %s | %s | %s\n",
                $row['TABLE_NAME'],
                $row['INDEX_NAME'],
                $row['COLUMNS']
            );
            $outputContent($line);
        }

        foreach ($this->inspector->getForeignKeyConstraints($dbName) as $row) {
            $line = \sprintf(
                "FOREIGN CONSTRAINT | %s | %s.%s | %s.%s\n",
                $row['CONSTRAINT_NAME'],
                \explode('/', $row['FOR_NAME'])[1],
                $row['FOR_COL_NAME'],
                \explode('/', $row['REF_NAME'])[1],
                $row['REF_COL_NAME']
            );
            $outputContent($line);
        }

        foreach ($this->inspector->getUniqueConstraints($dbName) as $row) {
            $line = \sprintf(
                "UNIQUE CONSTRAINT | %s | %s | %s\n",
                $row['CONSTRAINT_NAME'],
                $row['TABLE_NAME'],
                $row['COLUMNS']
            );
            $outputContent($line);
        }

        if ($jobs) {
            foreach ($this->inspector->getTableColumnValues('akeneo_batch_job_instance', 'label') as $row) {
                $outputContent(sprintf("VALUE:JOB:%s\n", $row['value']));
            }
        }

        return 0;
    }
}
