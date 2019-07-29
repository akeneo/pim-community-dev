<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeAndPersistProductCompletenesses
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CompletenessCalculatorInterface */
    private $completenessCalculator;

    /** @var SaveProductCompletenesses */
    private $saveProductCompletenesses;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CompletenessCalculatorInterface $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        // TODO Remove this
        $this->productRepository = $productRepository;
        $this->completenessCalculator = $completenessCalculator;
        $this->saveProductCompletenesses = $saveProductCompletenesses;
    }

    /**
     * @param string $productIdentifier
     */
    public function fromProductIdentifier(string $productIdentifier): void
    {
        $this->fromProductIdentifiers([$productIdentifier]);
    }

    /**
     * @param string[] $productIdentifiers
     */
    public function fromProductIdentifiers(array $productIdentifiers): void
    {
        $completenessCollections = $this->completenessCalculator->fromProductIdentifiers($productIdentifiers);

        foreach ($completenessCollections as $completenessCollection) {
            $this->saveProductCompletenesses->save($completenessCollection);
        }
    }
}
