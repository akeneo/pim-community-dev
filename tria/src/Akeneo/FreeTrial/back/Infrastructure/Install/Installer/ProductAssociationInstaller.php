<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

final class ProductAssociationInstaller implements FixtureInstaller
{
    private FixtureReader $fixtureReader;

    private IdentifiableObjectRepositoryInterface $productRepository;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    public function __construct(
        FixtureReader $fixtureReader,
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver
    ) {
        $this->fixtureReader = $fixtureReader;
        $this->productRepository = $productRepository;
        $this->updater = $updater;
        $this->saver = $saver;
    }

    public function install(): void
    {
        foreach ($this->fixtureReader->read() as $productData) {
            $product = $this->productRepository->findOneByIdentifier($productData['identifier']);

            if (!$product instanceof ProductInterface) {
                throw new \Exception(sprintf('Product "%s" not found', $productData['identifier']));
            }

            $this->updater->update($product, ['associations' => $productData['associations']]);
            $this->saver->save($product);
        }
    }
}
