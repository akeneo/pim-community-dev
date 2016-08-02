<?php

namespace Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

/**
 * DefaultParameters for product quick export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQuickExport implements DefaultValuesProviderInterface
{
    /** @var DefaultValuesProviderInterface */
    protected $simpleProvider;

    /** @var string[] */
    protected $supportedJobNames;

    /**
     * @param DefaultValuesProviderInterface $simpleProvider
     * @param string[]                       $supportedJobNames
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
        $parameters['filters'] = null;
        $parameters['mainContext'] = null;
        $parameters['selected_properties'] = null;
        $parameters['with_media'] = true;

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
