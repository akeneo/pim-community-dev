<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeAndPersistProductCompletenesses
{
    private const CHUNK_SIZE = 1000;

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
     * @param string[] $productIdentifiers
     * @deprecated
     */
    public function fromProductIdentifiers(array $productIdentifiers): void
    {
        foreach (array_chunk($productIdentifiers, self::CHUNK_SIZE) as $identifiersChunk) {
            $completenessCollections = $this->completenessCalculator->fromProductIdentifiers($identifiersChunk);
            $this->saveProductCompletenesses->saveAll($completenessCollections);
        }
    }

    /**
     * @param UuidInterface[] $productUuids
     */
    public function fromProductUuids(array $productUuids): void
    {
        foreach (array_chunk($productUuids, self::CHUNK_SIZE) as $uuidsChunk) {
            $completenessCollections = $this->completenessCalculator->fromProductUuids($uuidsChunk);
            $this->saveProductCompletenesses->saveAll($completenessCollections);
        }
    }
}
