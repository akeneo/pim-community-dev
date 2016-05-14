<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;

/**
 * Provides simple default values to setup any JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.7, this class is only used for backward compatibility, please register your own
 *             DefaultValuesProviderInterface in the DefaultValuesProviderRegistry
 */
class BackwardCompatibleDefaultValuesProvider implements DefaultValuesProviderInterface
{
    /** @var array */
    protected $defaultValues;

    /**
     * @param array $defaultValues
     */
    public function __construct(array $defaultValues)
    {
        $this->defaultValues = $defaultValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return true;
    }
}
