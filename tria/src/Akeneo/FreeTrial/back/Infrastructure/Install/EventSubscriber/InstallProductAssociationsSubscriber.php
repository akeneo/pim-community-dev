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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InstallProductAssociationsSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private IdentifiableObjectRepositoryInterface $productRepository;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver
    )
    {
        $this->productRepository = $productRepository;
        $this->updater = $updater;
        $this->saver = $saver;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURE => 'installProductAssociations',
        ];
    }

    public function installProductAssociations(InstallerEvent $installerEvent): void
    {
        if ('fixtures_product_csv' !== $installerEvent->getSubject()) {
            return;
        }

        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        $file = fopen($this->getProductAssociationsFixturesPath(), 'r');

        while ($line = fgets($file)) {
            $productData = json_decode($line, true);
            $product = $this->productRepository->findOneByIdentifier($productData['identifier']);

            if (!$product instanceof ProductInterface) {
                throw new \Exception(sprintf('Product "%s" not found', $productData['identifier']));
            }

            $this->updater->update($product, ['associations' => $productData['associations']]);
            $this->saver->save($product);
        }
    }
}
