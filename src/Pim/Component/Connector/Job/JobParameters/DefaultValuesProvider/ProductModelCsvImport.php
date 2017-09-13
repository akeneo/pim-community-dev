<?php

namespace Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;

/**
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCsvImport implements DefaultValuesProviderInterface
{
    /** @var DefaultValuesProviderInterface */
    private $simpleProvider;

    /** @var array */
    private $supportedJobNames;

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
    public function getDefaultValues(): array
    {
        $parameters = $this->simpleProvider->getDefaultValues();
        $parameters['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $parameters['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $parameters['enabled'] = true;
        $parameters['categoriesColumn'] = 'categories';
        $parameters['familyVariantColumn'] = 'family_variant';
        $parameters['enabledComparison'] = true;
        $parameters['realTimeVersioning'] = true;

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
