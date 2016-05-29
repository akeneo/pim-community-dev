<?php

namespace Akeneo\Component\Batch\Step;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Step\ItemStep;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Step instance factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * TODO: to be dropped
 */
class StepFactory
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /**
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher
     * @param JobRepositoryInterface   $jobRepository   Object responsible
     *                                                  for persisting jobExecution and stepExection states
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, JobRepositoryInterface $jobRepository)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->jobRepository   = $jobRepository;
    }

    /**
     * @param string $name
     * @param string $class
     * @param array  $services
     * @param array  $parameters
     *
     * @return ItemStep
     */
    public function createStep($name, $class, array $services, array $parameters)
    {
        $step = new $class($name, $this->eventDispatcher, $this->jobRepository);

        foreach ($services as $setter => $service) {
            $method = 'set'.Inflector::camelize($setter);
            $step->$method($service);
        }

        foreach ($parameters as $setter => $param) {
            $method = 'set'.Inflector::camelize($setter);
            $step->$method($param);
        }

        return $step;
    }
}
