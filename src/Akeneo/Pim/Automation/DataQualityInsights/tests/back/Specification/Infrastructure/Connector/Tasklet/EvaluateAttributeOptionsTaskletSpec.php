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

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributeOptions;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

final class EvaluateAttributeOptionsTaskletSpec extends ObjectBehavior
{
    public function let(EvaluateUpdatedAttributeOptions $evaluateUpdatedAttributeOptions, LoggerInterface $logger, StepExecution $stepExecution)
    {
        $this->beConstructedWith($evaluateUpdatedAttributeOptions, $logger);

        $stepExecution->getId()->willReturn(42);
        $this->setStepExecution($stepExecution);
    }

    public function it_does_not_crash_if_an_error_occurs_during_attribute_options_evaluations(
        $evaluateUpdatedAttributeOptions,
        $stepExecution
    ) {
        $exception = new \Exception('fail');
        $evaluateUpdatedAttributeOptions->evaluateAll()->willThrow($exception);
        $stepExecution->addFailureException($exception)->shouldBeCalled();

        $this->execute();
    }
}
