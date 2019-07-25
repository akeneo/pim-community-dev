<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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
        foreach ($this->getProducts($productIdentifiers) as $product) {
            $completenesses = $this->completenessCalculator->calculate($product);

            $collection = new ProductCompletenessCollection(
                $product->getId(),
                array_map(
                    function (CompletenessInterface $completeness): ProductCompleteness {
                        return new ProductCompleteness(
                            $completeness->getChannel()->getCode(),
                            $completeness->getLocale()->getCode(),
                            $completeness->getRequiredCount(),
                            $completeness->getMissingAttributes()->map(
                                function (AttributeInterface $missingAttribute): string {
                                    return $missingAttribute->getCode();
                                }
                            )->toArray()
                        );
                    },
                    $completenesses
                )
            );

            $this->saveProductCompletenesses->save($collection);
        }
    }

    /**
     * @param string[] $productIdentifiers
     *
     * @return ProductInterface[]
     */
    private function getProducts($productIdentifiers): array
    {
        return $this->productRepository->findBy(['identifier' => $productIdentifiers]);
    }
}
