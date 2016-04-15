<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;

/**
 * Provides simple default parameters to setup any JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleDefaultParameters implements DefaultParametersInterface
{
    /** @var array */
    protected $default;

    /**
     * @param array $default
     */
    public function __construct(array $default)
    {
        $this->default = $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->default;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return true;
    }
}
