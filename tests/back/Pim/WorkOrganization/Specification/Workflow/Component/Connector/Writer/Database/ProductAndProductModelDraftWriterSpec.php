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

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database\ProductAndProductModelDraftWriter;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database\ProductDraftWriter;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Platform\Bundle\ImportExportBundle\Factory\NotificationFactory;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class ProductAndProductModelDraftWriterSpec extends ObjectBehavior
{
    public function let(
        ProductDraftWriter $productDraftWriter,
        ProductDraftWriter $productModelDraftWriter,
        SimpleFactoryInterface $notificationFactory,
        NotifierInterface $notifier,
        JobExecution $jobExecution,
        StepExecution $currentStepExecution,
        StepExecution $firstStepExecution
    ) {
        $this->beConstructedWith(
            $productDraftWriter,
            $productModelDraftWriter,
            $notificationFactory,
            $notifier
        );

        $jobInstance = new JobInstance();
        $jobInstance->setLabel('Edit common attributes');

        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);

        $firstStepExecution = new StepExecution('perform', $jobExecution);
        $firstStepExecution->incrementSummaryInfo('read', 5);

        $currentStepExecution = new StepExecution('send_for_approval', $jobExecution);

        $this->setStepExecution($currentStepExecution);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelDraftWriter::class);
    }

    public function it_is_an_item_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    public function it_is_a_step_execution_aware()
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    public function it_writes_product_an_product_model_drafts(
        ProductDraftWriter $productDraftWriter,
        ProductDraftWriter $productModelDraftWriter,
        SimpleFactoryInterface $notificationFactory,
        NotifierInterface $notifier
    ) {
        $productDraftA = new ProductDraft();
        $productDraftA->setAuthor('Mary');
        $productDraftB = new ProductDraft();
        $productDraftB->setAuthor('Mary');
        $productModelDraft = new ProductModelDraft();
        $productModelDraft->setAuthor('Mary');

        $productDraftWriter->write([$productDraftA, $productDraftB])->shouldBeCalled();
        $productModelDraftWriter->write([$productModelDraft])->shouldBeCalled();

        $notification = new Notification();
        $notificationFactory->create()->willReturn($notification);
        $notifier->notify($notification, ['Mary']);

        $this->write([$productDraftA, $productModelDraft, $productDraftB]);
    }
}
