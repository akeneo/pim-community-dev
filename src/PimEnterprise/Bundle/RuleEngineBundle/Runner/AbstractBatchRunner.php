<?php


namespace PimEnterprise\Bundle\RuleEngineBundle\Runner;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\Process\PhpExecutableFinder;

abstract class AbstractBatchRunner implements RunnerInterface
{
    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /** @var string */
    protected $jobCode;

    /**
     * {@inheritdoc}
     */
    public function run(RuleInterface $rule)
    {
        if (null === $this->rootDir) {
            throw new \LogicException('Root directory can not be null.');
        }
        if (null === $this->environment) {
            throw new \LogicException('Environment can not be null.');
        }
        if (null === $this->jobCode) {
            throw new \LogicException('Job code directory can not be null.');
        }

        $pathFinder = new PhpExecutableFinder();

        // TODO: put this in a dedicated JobLauncher service
        $cmd = sprintf(
            '%s %s/console akeneo:batch:job --env=%s %s --config="{\"ruleCode\":\"%s\"}" >> %s/logs/batch_execute.log 2>&1',
            $pathFinder->find(),
            $this->rootDir,
            $this->environment,
            $this->jobCode,
            $rule->getCode(),
            $this->rootDir
        );

        echo sprintf('Launching command "%s"', $cmd);

        exec($cmd . ' &');
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param string $jobCode
     */
    public function setJobCode($jobCode)
    {
        $this->jobCode = $jobCode;
    }

    /**
     * @param string $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }
}
