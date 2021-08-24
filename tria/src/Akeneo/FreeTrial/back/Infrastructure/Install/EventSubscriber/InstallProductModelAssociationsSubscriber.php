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

namespace Akeneo\FreeTrial\Infrastructure\Install\EventSubscriber;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InstallProductModelAssociationsSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private IdentifiableObjectRepositoryInterface $productModelRepository;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    public function __construct(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver
    )
    {
        $this->productModelRepository = $productModelRepository;
        $this->updater = $updater;
        $this->saver = $saver;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURE => 'installProductModelAssociations',
        ];
    }

    public function installProductModelAssociations(InstallerEvent $installerEvent): void
    {
        // Install after products in case of association with a product.
        if ('fixtures_product_csv' !== $installerEvent->getSubject()) {
            return;
        }

        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        $file = fopen($this->getProductModelAssociationsFixturesPath(), 'r');

        while ($line = fgets($file)) {
            $productModelData = json_decode($line, true);
            $productModel = $this->productModelRepository->findOneByIdentifier($productModelData['code']);

            if (!$productModel instanceof ProductModelInterface) {
                throw new \Exception(sprintf('Product model "%s" not found', $productModelData['code']));
            }

            $this->updater->update($productModel, ['associations' => $productModelData['associations']]);
            $this->saver->save($productModel);
        }
    }
}
