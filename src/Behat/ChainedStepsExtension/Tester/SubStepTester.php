<?php

/*
 * This file is part of the Behat ChainedStepsExtension.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\ChainedStepsExtension\Tester;

use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\StepTester;
use Behat\ChainedStepsExtension\Step\SubStep;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Environment\Environment;

class SubStepTester implements StepTester
{
    private $baseTester;

    public function __construct(StepTester $baseTester)
    {
        $this->baseTester = $baseTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        return $this->baseTester->setUp($env, $feature, $step, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        $result = $this->baseTester->test($env, $feature, $step, $skip);

        if (!$result instanceof ExecutedStepResult || !$this->supportsResult($result->getCallResult())) {
            return $result;
        }

        $returnedValue = $result->getCallResult()->getReturn();

        if (!is_array($returnedValue)) {
            return $result;
        }

        return $this->runChainedSteps($env, $feature, $result, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, $skip, StepResult $result)
    {
        return $this->baseTester->tearDown($env, $feature, $step, $skip, $result);
    }

    private function supportsResult(CallResult $result)
    {
        $return = $result->getReturn();

        if ($return instanceof SubStep) {
            return true;
        }

        if (!is_array($return) || empty($return)) {
            return false;
        }

        foreach ($return as $value) {
            if (!$value instanceof SubStep) {
                return false;
            }
        }

        return true;
    }

    private function runChainedSteps(Environment $env, FeatureNode $feature, ExecutedStepResult $result, $skip)
    {
        $callResult = $result->getCallResult();
        $steps = $callResult->getReturn();

        if (!is_array($steps)) {
            $steps = array($steps);
        }

        /** @var SubStep[] $steps */

        foreach ($steps as $step) {
            $stepResult = $this->test($env, $feature, $step, $skip);

            if (!$stepResult->isPassed()) {
                return $stepResult;
            }
        }

        return $result;
    }
}
