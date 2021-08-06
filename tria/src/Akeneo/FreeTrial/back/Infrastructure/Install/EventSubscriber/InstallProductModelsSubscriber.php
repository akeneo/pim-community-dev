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
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class InstallProductModelsSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private SimpleFactoryInterface $factory;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    private ValidatorInterface $validator;

    public function __construct(
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURE => 'installProductModels',
        ];
    }

    public function installProductModels(InstallerEvent $installerEvent): void
    {
        if ('fixtures_product_model_csv' !== $installerEvent->getSubject()) {
            return;
        }

        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        $file = fopen($this->getProductModelsFixturesPath(), 'r');

        while ($line = fgets($file)) {
            $productModelData = json_decode($line, true);
            $this->addProductModel($productModelData);
        }
    }

    private function addProductModel(array $productModelData): void
    {
        $productModel = $this->factory->create();
        $this->updater->update($productModel, $productModelData);

        $violations = $this->validator->validate($productModel, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            throw new \Exception(sprintf('validation failed on product model "%s"', $productModelData['code']));
        }

        $this->saver->save($productModel);
    }
}
