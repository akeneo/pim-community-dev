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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProposalUpsert implements ProposalUpsertInterface
{
    /** @var array */
    private $drafts = [];

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /**
     * @param ObjectUpdaterInterface $productUpdater
     */
    public function __construct(ObjectUpdaterInterface $productUpdater)
    {
        $this->productUpdater = $productUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ProductInterface $product, array $values, string $author): void
    {
        $this->productUpdater->update($product, ['values' => $values]);

        $key = sprintf('%s-%s', $product->getIdentifier(), $author);
        $this->drafts[$key] = $product->getValues()->toArray();
    }

    /**
     * @param $identifier
     * @param $author
     *
     * @return bool
     */
    public function hasProposalForProduct($identifier, $author): bool
    {
        return array_key_exists(sprintf('%s-%s', $identifier, $author), $this->drafts);
    }
}
