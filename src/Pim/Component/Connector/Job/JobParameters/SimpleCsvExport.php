<?php

namespace Pim\Component\Connector\Job\JobParameters;

use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobParameters\DefaultParametersInterface;

/**
 * DefaultParameters for simple CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleCsvExport implements DefaultParametersInterface
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
    public function getParameters()
    {
        return [
            'filePath' => null,
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Job $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
