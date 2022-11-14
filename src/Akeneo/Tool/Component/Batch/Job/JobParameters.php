<?php

namespace Akeneo\Tool\Component\Batch\Job;

/**
 * Value object representing runtime parameters to a batch job. Because the parameters have no individual meaning
 * outside of the JobParameters they are contained within, it is a value object rather than an entity. Furthermore,
 * because these parameters will need to be persisted, it is vital that the types added are restricted.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.JobParameters;
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobParameters implements \IteratorAggregate, \Countable
{
    public function __construct(private array $parameters)
    {
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Returns the parameter value
     *
     * @throws UndefinedJobParameterException
     */
    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->parameters)) {
            throw new UndefinedJobParameterException(sprintf('Parameter "%s" is undefined', $key));
        }

        return $this->parameters[$key];
    }

    /**
     * Set the job parameter. This should never be used for a connector.
     * This is only for internal usage.
     *
     * @internal
     */
    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Returns the parameters array
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->parameters);
    }
}
