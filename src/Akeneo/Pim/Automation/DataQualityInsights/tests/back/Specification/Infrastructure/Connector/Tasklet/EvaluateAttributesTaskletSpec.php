<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributes;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class EvaluateAttributesTaskletSpec extends ObjectBehavior
{
    public function let(EvaluateUpdatedAttributes $evaluateUpdatedAttributes, LoggerInterface $logger, StepExecution $stepExecution)
    {
        $this->beConstructedWith($evaluateUpdatedAttributes, $logger);

        $stepExecution->getId()->willReturn(42);
        $this->setStepExecution($stepExecution);
    }

    public function it_does_not_crash_if_an_error_occurs_during_attributes_evaluations(
        $evaluateUpdatedAttributes,
        $stepExecution
    ) {
        $exception = new \Exception('fail');
        $evaluateUpdatedAttributes->evaluateAll()->willThrow($exception);
        $stepExecution->addFailureException($exception)->shouldBeCalled();

        $this->execute();
    }
}
