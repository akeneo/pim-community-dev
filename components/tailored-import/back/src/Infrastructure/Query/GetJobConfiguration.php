<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Query;

use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Domain\Model\JobConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Query\GetJobConfigurationInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class GetJobConfiguration implements GetJobConfigurationInterface
{
    public function __construct(
        private JobInstanceRepository $jobInstanceRepository,
    ) {
    }

    public function byJobCode(string $jobCode): JobConfiguration
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($jobCode);

        if (null === $jobInstance) {
            throw new \InvalidArgumentException(sprintf('Job instance with code "%s" does not exist', $jobCode));
        }

        return $this->getJobConfiguration($jobInstance);
    }

    private function getJobConfiguration(JobInstance $jobInstance): JobConfiguration
    {
        $jobInstanceParameters = $jobInstance->getRawParameters();

        $fileStructure = FileStructure::createFromNormalized($jobInstanceParameters['file_structure']);
        $columns = ColumnCollection::createFromNormalized($jobInstanceParameters['import_structure']['columns']);

        return new JobConfiguration(
            $jobInstanceParameters['file_key'],
            $fileStructure,
            $columns,
        );
    }
}
