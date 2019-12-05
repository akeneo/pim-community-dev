<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\PushStructureAndProductsToFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters\PushStructureAndProductsToFranklinParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

final class PushStructureAndProductsToFranklinTaskletSpec extends ObjectBehavior
{
    public function let(
        GetConnectionIsActiveHandler $connectionIsActiveHandler,
        PushStructureAndProductsToFranklin $pushStructureAndProductsToFranklin,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($connectionIsActiveHandler, $pushStructureAndProductsToFranklin);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
    }

    public function it_pushes_structure_and_products_to_franklin(
        GetConnectionIsActiveHandler $connectionIsActiveHandler,
        PushStructureAndProductsToFranklin $pushStructureAndProductsToFranklin,
        JobParameters $jobParameters
    )
    {
        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(true);

        $jobParameters->get(PushStructureAndProductsToFranklinParameters::ATTRIBUTES_BATCH_SIZE)->willReturn(20);
        $jobParameters->get(PushStructureAndProductsToFranklinParameters::FAMILIES_BATCH_SIZE)->willReturn(10);
        $jobParameters->get(PushStructureAndProductsToFranklinParameters::PRODUCTS_BATCH_SIZE)->willReturn(100);

        $pushStructureAndProductsToFranklin->push(
            new BatchSize(20),
            new BatchSize(10),
            new BatchSize(100)
        )->shouldBeCalled();

        $this->execute();
    }

    public function it_throws_an_exception_if_franklin_is_not_activated(GetConnectionIsActiveHandler $connectionIsActiveHandler)
    {
        $connectionIsActiveHandler->handle(new GetConnectionIsActiveQuery())->willReturn(false);

        $this->shouldThrow(\Exception::class)->during('execute');
    }
}
