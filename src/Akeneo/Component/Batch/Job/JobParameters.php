<?php

namespace Akeneo\Component\Batch\Job;

/**
 * Value object representing runtime parameters to a batch job. Because the parameters have no individual meaning
 * outside of the JobParameters they are contained within, it is a value object rather than an entity. Furthermore,
 * because these parameters will need to be persisted, it is vital that the types added are restricted.
 *
 * This class is immutable.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobParameters
{
    /** @var array */
    protected $parameters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter($key)
    {
        return isset($this->parameters[$key]);
    }

    /**
     * @param string $key
     *
     * @return mixed
     *
     * @throws UndefinedJobParameterException
     */
    public function getParameter($key)
    {
        if (!array_key_exists($key, $this->parameters)) {
            throw new UndefinedJobParameterException(sprintf('Parameter "%s" is undefined', $key));
        }

        return $this->parameters[$key];
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
