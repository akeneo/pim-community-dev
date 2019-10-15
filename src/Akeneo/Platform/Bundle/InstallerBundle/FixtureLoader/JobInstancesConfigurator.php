<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Tool\Component\Batch\Model\JobInstance;

/**
 * Configure the job instances that are used to install the PIM by setting the relevant file path for each job.
 *
 * In case of standard install, the file paths can be fetched from the application configuration (installer_data).
 *
 * In case of behat install, this configurator can also be used with a list of paths to use.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobInstancesConfigurator
{
    /** @var FixturePathProvider */
    protected $pathProvider;

    /**
     * @param FixturePathProvider $pathProvider
     */
    public function __construct(FixturePathProvider $pathProvider)
    {
        $this->pathProvider = $pathProvider;
    }

    /**
     * The standard method to configure job instances with files provided in an install fixtures set
     *
     * @throws \Exception
     * @return JobInstance[]
     */
    public function configureJobInstancesWithInstallerData(string $catalogPath, array $jobInstances)
    {
        $installerDataPath = $this->pathProvider->getFixturesPath($catalogPath);
        if (!is_dir($installerDataPath)) {
            throw new \Exception(sprintf('Path "%s" not found', $installerDataPath));
        }

        $configuredJobInstances = [];
        foreach ($jobInstances as $jobInstance) {
            $configuration = $jobInstance->getRawParameters();

            $configuration['filePath'] = sprintf('%s%s', $installerDataPath, $configuration['filePath']);
            if (!is_readable($configuration['filePath'])) {
                throw new \Exception(
                    sprintf(
                        'The job "%s" can\'t be processed because the file "%s" is not readable',
                        $jobInstance->getCode(),
                        $configuration['filePath']
                    )
                );
            }
            $jobInstance->setRawParameters($configuration);
            $configuredJobInstances[] = $jobInstance;
        }

        return $configuredJobInstances;
    }

    /**
     * An alternative methods with configure job instance with replacement paths, please note that we can configure
     * here several job instances for a same job, for instance loading users.csv with a Community Edition file and
     * with an Enterprise Edition file
     *
     * @param JobInstance[] $jobInstances
     * @param array $replacePaths
     * @throws \Exception
     * @return JobInstance[]
     */
    public function configureJobInstancesWithReplacementPaths(array $jobInstances, array $replacePaths)
    {
        $counter = 0;

        $configuredJobInstances = [];
        foreach ($jobInstances as $jobInstance) {
            $configuration = $jobInstance->getRawParameters();

            if (!isset($replacePaths[$configuration['filePath']])) {
                throw new \Exception(sprintf('No replacement path for "%s"', $configuration['filePath']));
            }
            foreach ($replacePaths[$configuration['filePath']] as $replacePath) {
                $configuredJobInstance = clone $jobInstance;
                $configuredJobInstance->setCode($configuredJobInstance->getCode().''.$counter++);
                $configuration['filePath'] = $replacePath;
                if (!is_readable($configuration['filePath'])) {
                    throw new \Exception(
                        sprintf(
                            'The job "%s" can\'t be processed because the file "%s" is not readable',
                            $configuredJobInstance->getCode(),
                            $configuration['filePath']
                        )
                    );
                }
                $configuredJobInstance->setRawParameters($configuration);
                $configuredJobInstances[] = $configuredJobInstance;
            }
        }

        return $configuredJobInstances;
    }
}
