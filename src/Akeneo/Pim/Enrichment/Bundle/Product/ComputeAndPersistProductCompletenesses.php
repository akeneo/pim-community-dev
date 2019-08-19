<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeAndPersistProductCompletenesses
{
    /** @var CompletenessCalculator */
    private $completenessCalculator;

    /** @var SaveProductCompletenesses */
    private $saveProductCompletenesses;

    public function __construct(
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
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

        $this->saveProductCompletenesses->saveAll($completenessCollections);
    }
}
