<?php

namespace Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * DefaultParameters for product XLSX export
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductXlsxExport implements DefaultValuesProviderInterface
{
    /** @var DefaultValuesProviderInterface */
    protected $simpleProvider;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param DefaultValuesProviderInterface $simpleProvider
     * @param array                          $supportedJobNames
     */
    public function __construct(DefaultValuesProviderInterface $simpleProvider, array $supportedJobNames)
    {
        $this->simpleProvider = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        $parameters = $this->simpleProvider->getDefaultValues();
        $parameters['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $parameters['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $parameters['channel'] = null;
        $parameters['linesPerFile'] = 10000;

        $parameters['filters'] = [
            'enabled' => [
                'operator' => Operators::EQUALS,
                'value'    => true,
                'context'  => []
            ],
            'updated' => null,
        ];

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
