<?php

namespace Akeneo\Bundle\BatchBundle\Step;

use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Component\Batch\Step\ItemStep;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Step instance factory
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class StepFactory
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @®ar DoctrineJobRepository
     */
    protected $jobRepository;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param DoctrineJobRepository    $jobRepository
     */
    public function __construct($eventDispatcher, $jobRepository)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->jobRepository   = $jobRepository;
    }

    /**
     * @param string $title
     * @param string $class
     * @param array  $services
     * @param array  $parameters
     *
     * @return ItemStep
     */
    public function createStep($title, $class, array $services, array $parameters)
    {
        $step = new $class($title);
        $step->setEventDispatcher($this->eventDispatcher);
        $step->setJobRepository($this->jobRepository);

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
