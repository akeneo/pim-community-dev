<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Command;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

final class BuildJobsYamlCommand extends Command
{
    use InstallCatalogTrait;

    protected function configure()
    {
        $description = <<<EOL
Build the yaml file to install the import/export jobs in the Free-Trial catalog.
  The source must be a CSV with header and tab as column separator, that contains the result of this query:
  SELECT code, label, job_name, connector, type, raw_parameters FROM akeneo_batch_job_instance WHERE type IN ('import', 'export');
  Tip: use options "--raw" and "--silent" with the MySQL command cli (mysql --raw --silent --execute [query])
EOL;

        $this
            ->setName('akeneo:free-trial:build-jobs-yaml')
            ->setDescription($description)
            ->addArgument('source-file', InputArgument::REQUIRED, "Absolute file path of the source of the jobs.")
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start build jobs yaml file...');

        $sourceFile = fopen($input->getArgument('source-file'), 'r');

        if (false === $sourceFile) {
            throw new \Exception(sprintf('Enable to read source file %s', $input->getArgument('source-file')));
        }

        $header = fgetcsv($sourceFile, 0, "\t");
        if ('code' !== $header[0]) {
            throw new \Exception('The CSV source file must have header and tab separator.');
        }

        $jobs = [];
        while ($row = fgetcsv($sourceFile, 0, "\t")) {
            $jobData = array_combine($header, $row);
            $jobs[$jobData['code']] = [
                'connector' => $jobData['connector'],
                'alias' => $jobData['job_name'],
                'label' => $jobData['label'],
                'type' => $jobData['type'],
                'configuration' => unserialize($jobData['raw_parameters']),
            ];
        }

        $fileContent = Yaml::dump(['jobs' => $jobs], 10);

        file_put_contents($this->getJobsFixturesPath(), $fileContent);

        $output->writeln(sprintf('Import/export jobs have been extracted in %s', $this->getJobsFixturesPath()));

        return 0;
    }
}
