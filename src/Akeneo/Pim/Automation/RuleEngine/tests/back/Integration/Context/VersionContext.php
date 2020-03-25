<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class VersionContext implements Context
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        VersionRepositoryInterface $versionRepository
    ) {
        $this->productRepository = $productRepository;
        $this->versionRepository = $versionRepository;
    }

    /**
     * @Then /^the history of the product "([^"]*)" has ([^"]*) updates?$/
     */
    public function theHistoryOfTheProductHasUpdates(string $identifier, int $expectedUpdateCount): void
    {
        $product = $this->getProduct($identifier);
        $entries = $this->versionRepository->getLogEntries(Product::class, $product->getId());
        $count = null === $entries ? 0 : count($entries);

        Assert::same(
            $count,
            $expectedUpdateCount,
            sprintf('Expecting "%d" versions, having "%d".', $expectedUpdateCount, $count)
        );
    }

    /**
     * @Then /^a? ?versions? of the "([^"]*)" product should be:$/
     */
    public function versionOfTheProductShouldBe(string $identifier, TableNode $table): void
    {
        $product = $this->getProduct($identifier);
        $versions = $this->versionRepository->getLogEntries(Product::class, $product->getId());

        foreach ($table->getHash() as $expectingData) {
            /** @var Version $version */
            foreach ($versions as $version) {
                if ($version->getVersion() !== (int) $expectingData['version']) {
                    continue;
                }

                Assert::same($version->getContext(), $expectingData['context']);

                $changeset = $version->getChangeset();
                Assert::keyExists(
                    $changeset,
                    $expectingData['property'],
                    sprintf('Expected the key "%s" to exist, having "%s".', $expectingData['property'], join(', ', array_keys($changeset)))
                );
                Assert::same($changeset[$expectingData['property']]['new'], $expectingData['new_value']);

                continue 2;
            }

            throw new \Exception(
                sprintf('Version number "%d" is not found for this product.', $expectingData['version'])
            );
        }
    }

    private function getProduct(string $identifier): ProductInterface
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \RuntimeException(sprintf('Product with identifier "%s" is not found.', $identifier));
        }

        return $product;
    }
}
