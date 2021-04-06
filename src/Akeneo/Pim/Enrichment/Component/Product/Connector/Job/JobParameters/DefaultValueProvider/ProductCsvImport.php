<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Pim\Enrichment\Component\ContextOrigin;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;

/**
 * DefaultParameters for product CSV import
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvImport implements DefaultValuesProviderInterface
{
    protected DefaultValuesProviderInterface $simpleProvider;
    protected array $supportedJobNames;

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
        $parameters['familyColumn'] = 'family';
        $parameters['groupsColumn'] = 'groups';
        $parameters['enabledComparison'] = true;
        $parameters['realTimeVersioning'] = true;
        $parameters['convertVariantToSimple'] = false;
        $parameters['origin'] = ContextOrigin::IMPORT;

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
