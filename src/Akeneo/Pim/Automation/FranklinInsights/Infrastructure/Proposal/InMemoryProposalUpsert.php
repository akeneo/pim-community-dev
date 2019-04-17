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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProposalUpsert implements ProposalUpsertInterface
{
    /** @var array */
    private $drafts = [];

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ObjectUpdaterInterface $productUpdater
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $suggestedData, string $author): int
    {
        $processed = 0;
        foreach ($suggestedData as $data) {
            $product = $this->productRepository->find($data->getProductId()->toInt());
            $this->productUpdater->update($product, ['values' => $data->getSuggestedValues()]);

            $key = sprintf('%s-%s', $product->getIdentifier(), $author);
            $this->drafts[$key] = $product->getValues()->toArray();
            $processed++;
        }

        return $processed;
    }

    /**
     * @param string $identifier
     * @param string $author
     *
     * @return bool
     */
    public function hasProposalForProduct($identifier, $author): bool
    {
        return array_key_exists(sprintf('%s-%s', $identifier, $author), $this->drafts);
    }

    /**
     * @return bool
     */
    public function hasProposal(): bool
    {
        return !empty($this->drafts);
    }
}
