<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ResubscribeProductsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectNonNullRequestedIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValuesCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet\IdentifyProductToResubscribeTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IdentifyProductToResubscribeTaskletSpec extends ObjectBehavior
{
    public function let(
        SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery,
        SelectNonNullRequestedIdentifiersQueryInterface $selectNonNullRequestedIdentifiersQuery,
        ResubscribeProductsInterface $resubscribeProducts,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ): void {
        $this->beConstructedWith(
            $selectProductIdentifierValuesQuery,
            $selectNonNullRequestedIdentifiersQuery,
            $resubscribeProducts
        );

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_is_an_identify_products_to_resubscribe_tasklet(): void
    {
        $this->shouldHaveType(IdentifyProductToResubscribeTasklet::class);
    }

    public function it_does_nothing_if_no_subscription_has_requested_identifier(
        $selectNonNullRequestedIdentifiersQuery,
        $resubscribeProducts,
        $jobParameters
    ): void {
        $jobParameters->get('updated_identifiers')->willReturn(['brand', 'mpn']);
        $selectNonNullRequestedIdentifiersQuery->execute(['brand', 'mpn'], 0, 100)->willReturn([]);

        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->execute();
    }

    public function it_identifies_products_which_need_resubscribing_after_an_identifiers_mapping_update(
        $selectProductIdentifierValuesQuery,
        $selectNonNullRequestedIdentifiersQuery,
        $resubscribeProducts,
        $jobParameters
    ): void {
        $jobParameters->get('updated_identifiers')->willReturn(['asin']);
        $selectNonNullRequestedIdentifiersQuery->execute(['asin'], 0, 100)->willReturn([
            42 => ['asin' => 'ABC123'],
            44 => ['asin' => 'DEF456'],
        ]);
        $selectNonNullRequestedIdentifiersQuery->execute(['asin'], 44, 100)->willReturn([
            56 => ['asin' => 'FGH789', 'upc' => '123456789'],
        ]);
        $selectNonNullRequestedIdentifiersQuery->execute(['asin'], 56, 100)->willReturn([]);

        $newIdentifierValuesCollection = new ProductIdentifierValuesCollection();
        $newIdentifierValuesCollection->add(new ProductIdentifierValues(42, ['asin' => 'TYU654']));
        $newIdentifierValuesCollection->add(new ProductIdentifierValues(44, ['asin' => 'DEF456']));
        $selectProductIdentifierValuesQuery->execute([42, 44])->willReturn($newIdentifierValuesCollection);

        $nextIdentifierValuesCollection = new ProductIdentifierValuesCollection();
        $nextIdentifierValuesCollection->add(new ProductIdentifierValues(56, []));
        $selectProductIdentifierValuesQuery->execute([56])->willReturn($nextIdentifierValuesCollection);

        $resubscribeProducts->process([42, 56])->shouldBeCalled();

        $this->execute();
    }
}
