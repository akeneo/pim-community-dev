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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

final class ProductModelAssociationInstaller implements FixtureInstaller
{
    private FixtureReader $fixtureReader;

    private IdentifiableObjectRepositoryInterface $productModelRepository;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    public function __construct(
        FixtureReader $fixtureReader,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver
    ) {
        $this->fixtureReader = $fixtureReader;
        $this->productModelRepository = $productModelRepository;
        $this->updater = $updater;
        $this->saver = $saver;
    }

    public function install(): void
    {
        foreach ($this->fixtureReader->read() as $productModelData) {
            $productModel = $this->productModelRepository->findOneByIdentifier($productModelData['code']);

            if (!$productModel instanceof ProductModelInterface) {
                throw new \Exception(sprintf('Product model "%s" not found', $productModelData['code']));
            }

            $this->updater->update($productModel, ['associations' => $productModelData['associations']]);
            $this->saver->save($productModel);
        }
    }
}
