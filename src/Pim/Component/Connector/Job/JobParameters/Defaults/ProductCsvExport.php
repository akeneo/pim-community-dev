<?php

namespace Pim\Component\Connector\Job\JobParameters\Defaults;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultParametersInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;

/**
 * DefaultParameters for product CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvExport implements DefaultParametersInterface
{
    /** @var DefaultParametersInterface */
    protected $simpleParameters;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param DefaultParametersInterface $simpleParameters
     * @param array                      $supportedJobNames
     */
    public function __construct(DefaultParametersInterface $simpleParameters, array $supportedJobNames)
    {
        $this->simpleParameters = $simpleParameters;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $parameters = $this->simpleParameters->getParameters();
        $parameters['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $parameters['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $parameters['channel'] = null;

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
