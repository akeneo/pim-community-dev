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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Reader\Database;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ProductDraftReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface, TrackableItemReaderInterface
{
    /** @var ItemReaderInterface */
    private $productReader;

    /** @var StepExecution */
    private $stepExecution;

    /** @var string|null */
    private $user;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productDraftRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productModelDraftRepository;

    public function __construct(
        ItemReaderInterface $productReader,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository
    ) {
        $this->productReader = $productReader;
        $this->productDraftRepository = $productDraftRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
    }

    public function initialize()
    {
        if (!$this->shouldSendForApproval()) {
            return;
        }

        $this->user = $this->stepExecution->getJobExecution()->getUser();

        if ($this->productReader instanceof InitializableInterface) {
            $this->productReader->initialize();
        }
    }

    public function read()
    {
        if (!$this->shouldSendForApproval()) {
            return null;
        }

        return $this->getNextDraft();
    }

    private function getNextDraft(): ?EntityWithValuesDraftInterface
    {
        $product = $this->productReader->read();
        if (null === $product) {
            return null;
        }

        $draft = $this->retrieveDraftFromProduct($product);

        return null === $draft ? $this->getNextDraft() : $draft;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        if ($this->productReader instanceof StepExecutionAwareInterface) {
            $this->productReader->setStepExecution($stepExecution);
        }
    }

    private function retrieveDraftFromProduct($product): ?EntityWithValuesDraftInterface
    {
        return $product instanceof ProductModelInterface
            ? $this->productModelDraftRepository->findUserEntityWithValuesDraft($product, $this->user)
            : $this->productDraftRepository->findUserEntityWithValuesDraft($product, $this->user);
    }

    private function shouldSendForApproval(): bool
    {
        $jobActions = $this->stepExecution->getJobParameters()->get('actions');

        return isset($jobActions[0]['sendForApproval']) && true === $jobActions[0]['sendForApproval'];
    }

    public function totalItems(): int
    {
        if (!$this->shouldSendForApproval() || !$this->productReader instanceof TrackableItemReaderInterface) {
            return 0;
        }

        return $this->productReader->totalItems();
    }
}
