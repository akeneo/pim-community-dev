<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Domain\FixtureLoad;

use Akeneo\Tool\Component\Batch\Model\JobInstance;

final class JobInstanceConfigurator
{
    /**
     * @param JobInstance[] $jobInstances
     *
     * @return JobInstance[]
     *
     * @throws \Exception
     */
    public static function configure(string $installerDataPath, array $jobInstances): array
    {
        if (!is_dir($installerDataPath)) {
            throw new \Exception(sprintf('Path "%s" not found', $installerDataPath));
        }

        $configuredJobInstances = [];
        foreach ($jobInstances as $jobInstance) {
            $configuration = $jobInstance->getRawParameters();

            $configuration['storage']['file_path'] = sprintf('%s%s', $installerDataPath, $configuration['storage']['file_path']);
            if (!is_readable($configuration['storage']['file_path'])) {
                throw new \Exception(sprintf('The job "%s" can\'t be processed because the file "%s" is not readable', $jobInstance->getCode(), $configuration['storage']['file_path']));
            }
            $jobInstance->setRawParameters($configuration);
            $configuredJobInstances[] = $jobInstance;
        }

        return $configuredJobInstances;
    }
}
