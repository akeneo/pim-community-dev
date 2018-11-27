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

namespace Akeneo\Test\Pim\Automation\SuggestData\EndToEnd\Context;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Repository\EntityWithValuesDraftRepository;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Behat\Behat\Context\Context;
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

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param EntityWithValuesDraftRepository $draftRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EntityWithValuesDraftRepository $draftRepository
    ) {
        $this->productRepository = $productRepository;
        $this->draftRepository = $draftRepository;
    }

    /**
     * @Then there should be a proposal for product :identifier
     *
     * @param string îdentifier
     */
    public function thereShouldBeOneProposalForProduct(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        $drafts = $this->draftRepository->findByEntityWithValues($product);

        Assert::count($drafts, 1);
        foreach ($drafts as $draft) {
            Assert::same(EntityWithValuesDraftInterface::READY, $draft->getStatus());
        }
    }
}
