<?php

namespace Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

/**
 * DefaultParameters for simple YAML export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleYamlExport implements DefaultValuesProviderInterface
{
    /** @var array */
    protected $supportedJobNames;

    /**
     * @param array $supportedJobNames
     */
    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        return [
            'filePath'              => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_%job_label%_%datetime%.yml',
            'user_to_notify'        => null,
            'is_user_authenticated' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
