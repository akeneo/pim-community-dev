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

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Reader\Database;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Reader\Database\ProductDraftReader;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class ProductDraftReaderSpec extends ObjectBehavior
{
    public function let(
        ItemReaderInterface $productReader,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        StepExecution $stepExecution,
        JobExecution $jobExecution
    ) {
        $this->beConstructedWith($productReader, $productDraftRepository, $productModelDraftRepository);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('mary');

        $jobParameters = new JobParameters([
            'actions' => [['sendForApproval' => true]]
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $this->setStepExecution($stepExecution);
        $this->initialize();
    }

    public function it_is_a_product_draft_reader()
    {
        $this->shouldImplement(ItemReaderInterface::class);
        $this->shouldHaveType(ProductDraftReader::class);
    }

    public function it_return_the_draft_of_a_product_if_there_is_one(
        ItemReaderInterface $productReader,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftInterface $productDraft,
        ProductInterface $product
    ) {
        $productReader->read()->willReturn($product);
        $productDraftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($productDraft);

        $this->read()->shouldReturn($productDraft);
    }

    public function it_return_the_draft_of_a_product_model_if_there_is_one(
        ItemReaderInterface $productReader,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        EntityWithValuesDraftInterface $productDraft,
        ProductModelInterface $productModel
    ) {
        $productReader->read()->willReturn($productModel);
        $productModelDraftRepository->findUserEntityWithValuesDraft($productModel, 'mary')->willReturn($productDraft);

        $this->read()->shouldReturn($productDraft);
    }

    public function it_return_null_if_there_is_no_product_to_read(ItemReaderInterface $productReader)
    {
        $productReader->read()->willReturn(null);

        $this->read()->shouldReturn(null);
    }

    public function it_reads_products_until_there_is_a_draft(
        ItemReaderInterface $productReader,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftInterface $productDraft,
        ProductInterface $productWithoutDraft,
        ProductInterface $productWithDraft
    ) {
        $productReader->read()->willReturn($productWithoutDraft, $productWithDraft);
        $productDraftRepository->findUserEntityWithValuesDraft($productWithoutDraft, 'mary')->willReturn(null);
        $productDraftRepository->findUserEntityWithValuesDraft($productWithDraft, 'mary')->willReturn($productDraft);

        $this->read()->shouldReturn($productDraft);
    }

    public function it_returns_null_if_no_product_has_a_draft(
        ItemReaderInterface $productReader,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        ProductInterface $productWithoutDraft
    ) {
        $productReader->read()->willReturn($productWithoutDraft, null);
        $productDraftRepository->findUserEntityWithValuesDraft($productWithoutDraft, 'mary')->willReturn(null);

        $this->read()->shouldReturn(null);
    }

    public function it_does_nothing_if_no_draft_should_be_sent_for_approval(
        StepExecution $stepExecution,
        ItemReaderInterface $productReader
    ) {
        $jobParameters = new JobParameters([
            'actions' => [['sendForApproval' => false]]
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);

        $productReader->read()->shouldNotBeCalled();

        $this->read()->shouldReturn(null);
    }

    public function it_does_nothing_if_the_action_send_for_approval_is_not_defined(
        StepExecution $stepExecution,
        ItemReaderInterface $productReader
    ) {
        $jobParameters = new JobParameters([
            'actions' => [[]]
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);

        $productReader->read()->shouldNotBeCalled();

        $this->read()->shouldReturn(null);
    }
}
