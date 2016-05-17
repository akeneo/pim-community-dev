<?php

namespace Akeneo\Component\Batch\Job;

/**
 * Value object representing runtime parameters to a batch job. Because the parameters have no individual meaning
 * outside of the JobParameters they are contained within, it is a value object rather than an entity. Furthermore,
 * because these parameters will need to be persisted, it is vital that the types added are restricted.
 *
 * This class is immutable.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.JobParameters;
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobParameters implements \IteratorAggregate, \Countable
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
     * Checks if the parameter is defined
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Returns the parameter value
     *
     * @param string $key
     *
     * @throws UndefinedJobParameterException
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->parameters)) {
            throw new UndefinedJobParameterException(sprintf('Parameter "%s" is undefined', $key));
        }

        return $this->parameters[$key];
    }

    /**
     * Returns the parameters array
     *
     * @return array
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->parameters);
    }
}
