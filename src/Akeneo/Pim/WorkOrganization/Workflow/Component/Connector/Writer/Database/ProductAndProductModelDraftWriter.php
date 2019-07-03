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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;

class ProductAndProductModelDraftWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var ProductDraftWriter */
    private $productDraftWriter;

    /** @var ProductDraftWriter */
    private $productModelDraftWriter;

    /** @var SimpleFactoryInterface */
    private $notificationFactory;

    /** @var NotifierInterface */
    private $notifier;

    /** @var StepExecution */
    private $stepExecution;

    public function __construct(
        ProductDraftWriter $productDraftWriter,
        ProductDraftWriter $productModelDraftWriter,
        SimpleFactoryInterface $notificationFactory,
        NotifierInterface $notifier
    ) {
        $this->productDraftWriter = $productDraftWriter;
        $this->productModelDraftWriter = $productModelDraftWriter;
        $this->notifier = $notifier;
        $this->notificationFactory = $notificationFactory;
    }

    public function write(array $items)
    {
        $productDrafts = array_values(array_filter($items, function ($item) {
            return $item instanceof ProductDraft;
        }));
        $productModelDrafts = array_values(array_filter($items, function ($item) {
            return $item instanceof ProductModelDraft;
        }));

        $countProductAndProductModelDraftsSent = 0;
        $author = null;

        if (!empty($productDrafts)) {
            $this->productDraftWriter->write($productDrafts);
            $countProductAndProductModelDraftsSent += count($productDrafts);
            $author = $productDrafts[0]->getAuthor();
        }
        if (!empty($productModelDrafts)) {
            $this->productModelDraftWriter->write($productModelDrafts);
            $countProductAndProductModelDraftsSent += count($productModelDrafts);
            $author = null === $author ? $productModelDrafts[0]->getAuthor() : $author;
        }

        if (0 < $countProductAndProductModelDraftsSent && null !== $author) {
            $this->notifyUser($countProductAndProductModelDraftsSent, $author);
        }
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
        $this->productDraftWriter->setStepExecution($stepExecution);
        $this->productModelDraftWriter->setStepExecution($stepExecution);
    }

    private function notifyUser(int $countProductAndProductModelDraftsWritten, string $author): void
    {
        $notification = $this->notificationFactory->create();
        $notification
            ->setType('success')
            ->setMessage('pimee_workflow.product_draft.notification.mass_edit_sent_for_approval')
            ->setMessageParams(
                [
                    '%jobLabel%'             => $this->getJobLabel(),
                    '%sentForApprovalCount%' => $countProductAndProductModelDraftsWritten,
                    '%readProductsCount%'    => $this->getProductsCount(),
                ]
            )
            ->setContext(['actionType' => 'pimee_workflow_product_draft_notification_sent']);

        $this->notifier->notify($notification, [$author]);
    }

    private function getProductsCount(): int
    {
        $stepExecutions = $this->stepExecution->getJobExecution()->getStepExecutions();
        /** @var StepExecution $firstStepExecution */
        $firstStepExecution = $stepExecutions->get(0);
        return $firstStepExecution->getSummaryInfo('read');

        return $firstStepExecution->getReadCount();
    }

    private function getJobLabel(): string
    {
        return $this->stepExecution->getJobExecution()->getJobInstance()->getLabel();
    }
}
