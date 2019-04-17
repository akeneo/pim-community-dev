<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\EndToEnd\Context;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository\EntityWithValuesDraftRepository;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProposalContext implements Context
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var EntityWithValuesDraftRepository */
    private $draftRepository;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param EntityWithValuesDraftRepository $draftRepository
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param EntityManager $entityManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EntityWithValuesDraftRepository $draftRepository,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        EntityManager $entityManager
    ) {
        $this->productRepository = $productRepository;
        $this->draftRepository = $draftRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Then there should be a proposal for product :identifier
     *
     * @param string $identifier
     */
    public function thereShouldBeAProposalForProduct(string $identifier): void
    {
        /*
         * Clearing the Doctrine entity manager is necessary before calling the repositories to avoid cache issues
         * A previous spin guarantees that the Ajax request is finished at this step, so there's no need to re-use a spin here
         */
        $this->entityManager->clear();

        $product = $this->productRepository->findOneByIdentifier($identifier);
        $draft = $this->draftRepository->findUserEntityWithValuesDraft($product, ProposalAuthor::USERNAME);

        $this->assertDraftIsProposed($draft);
        $this->assertSuggestedDataHaveBeenEmptied($product);
    }

    /**
     * @param EntityWithValuesDraftInterface $draft
     */
    private function assertDraftIsProposed(EntityWithValuesDraftInterface $draft): void
    {
        Assert::same(EntityWithValuesDraftInterface::READY, $draft->getStatus());
    }

    /**
     * @param ProductInterface $product
     */
    private function assertSuggestedDataHaveBeenEmptied(ProductInterface $product): void
    {
        $subscription = $this->productSubscriptionRepository->findOneByProductId(new ProductId($product->getId()));
        Assert::true($subscription->getSuggestedData()->isEmpty());
    }
}
