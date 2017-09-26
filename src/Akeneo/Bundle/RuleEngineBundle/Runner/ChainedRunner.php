<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Runner;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Chained rule runner. Gets the runner able to handle a rule from the runner
 * registry, and run it.
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChainedRunner implements DryRunnerInterface, BulkDryRunnerInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var LoggerInterface */
    protected $logger;

    /** @var bool */
    protected $stopOnError;

    /** @var RunnerInterface[] ordered runner with priority */
    protected $runners;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface          $logger
     * @param bool                     $stopOnError
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger, $stopOnError)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->stopOnError = $stopOnError;
    }

    /**
     * Adds a runner.
     *
     * @param RunnerInterface $runner
     *
     * @return ChainedRunner
     */
    public function addRunner(RunnerInterface $runner): ChainedRunner
    {
        $this->runners[] = $runner;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleDefinitionInterface $definition): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function run(RuleDefinitionInterface $definition, array $options = [])
    {
        $result = null;

        $this->eventDispatcher->dispatch(RuleEvents::PRE_EXECUTE, new GenericEvent($definition));

        try {
            $runner = $this->getRunner($definition);
            if (null !== $runner) {
                $result = $runner->run($definition, $options);
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_EXECUTE, new GenericEvent($definition));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function runAll(array $definitions, array $options = []): array
    {
        $results = [];

        $this->eventDispatcher->dispatch(RuleEvents::PRE_EXECUTE_ALL, new GenericEvent($definitions));

        foreach ($definitions as $definition) {
            $results[$definition->getCode()] = $this->run($definition, $options);
        }

        $this->eventDispatcher->dispatch(
            RuleEvents::POST_EXECUTE_ALL,
            new GenericEvent($definitions, $options)
        );

        return $results;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function dryRun(RuleDefinitionInterface $definition, array $options = []): ?RuleSubjectSetInterface
    {
        $result = null;

        try {
            $runner = $this->getDryRunner($definition);
            if (null !== $runner) {
                $result = $runner->dryRun($definition, $options);
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function dryRunAll(array $definitions, array $options = []): array
    {
        $results = [];

        foreach ($definitions as $definition) {
            $results[$definition->getCode()] = $this->dryRun($definition, $options);
        }

        return $results;
    }

    /**
     * Gets the runner supporting the given rule definition.
     *
     * @param RuleDefinitionInterface $definition
     *
     * @throws \LogicException
     *
     * @return RunnerInterface
     */
    protected function getRunner(RuleDefinitionInterface $definition): RunnerInterface
    {
        foreach ($this->runners as $runner) {
            if ($runner->supports($definition)) {
                return $runner;
            }
        }

        throw new \LogicException(sprintf('No runner available for the rule "%s".', $definition->getCode()));
    }

    /**
     * Gets the dry runner supporting the given rule definition.
     *
     * @param RuleDefinitionInterface $definition
     *
     * @throws \LogicException
     *
     * @return DryRunnerInterface
     */
    protected function getDryRunner(RuleDefinitionInterface $definition): DryRunnerInterface
    {
        foreach ($this->runners as $runner) {
            if ($runner instanceof DryRunnerInterface && $runner->supports($definition)) {
                return $runner;
            }
        }

        throw new \LogicException(sprintf('No dry runner available for the rule "%s".', $definition->getCode()));
    }

    /**
     * @param \Exception $e
     *
     * @throws \Exception
     */
    protected function handleException(\Exception $e): void
    {
        if (true === $this->stopOnError) {
            throw $e;
        }

        $this->logger->error($e->getMessage());
    }
}
