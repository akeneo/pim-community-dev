<?php

namespace Pim\Component\Connector\Job\JobParameters\Defaults;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultParametersInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;

/**
 * DefaultParameters for variant group CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupCsvExport implements DefaultParametersInterface
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
            'decimalSeparator' => LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR,
            'dateFormat' => LocalizerInterface::DEFAULT_DATE_FORMAT,
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
