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
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class InstallProductsSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private ProductBuilderInterface $productBuilder;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    private ValidatorInterface $productValidator;

    public function __construct(
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $productValidator
    )
    {
        $this->productBuilder = $productBuilder;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->productValidator = $productValidator;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURE => 'installProducts',
        ];
    }

    public function installProducts(InstallerEvent $installerEvent): void
    {
        if ('fixtures_product_csv' !== $installerEvent->getSubject()) {
            return;
        }

        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        $file = fopen($this->getProductsFixturesPath(), 'r');

        while ($line = fgets($file)) {
            $productData = json_decode($line, true);
            $productData['values']['sku'] = [[
                'locale' => null,
                'scope' => null,
                'data' => $productData['identifier'],
            ]];
            $this->addProduct($productData);
        }
    }

    private function addProduct(array $productData): void
    {
        $product = $this->productBuilder->createProduct();
        $this->updater->update($product, $productData);

        $violations = $this->productValidator->validate($product, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            throw new \Exception(sprintf('validation failed on product "%s"', $productData['identifier']));
        }

        $this->saver->save($product);
    }
}
