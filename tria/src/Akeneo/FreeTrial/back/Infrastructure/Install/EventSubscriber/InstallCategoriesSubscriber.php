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

final class InstallCategoriesSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private SimpleFactoryInterface $factory;

    private ObjectUpdaterInterface $updater;

    private ValidatorInterface $validator;

    private SaverInterface $saver;

    public function __construct(
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURE => 'installCategories',
        ];
    }

    public function installCategories(InstallerEvent $installerEvent): void
    {
        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        if ('fixtures_category_csv' !== $installerEvent->getSubject()) {
            return;
        }

        $categoriesFile = fopen($this->getCategoriesFixturesPath(), 'r');

        while ($categoryData = fgets($categoriesFile)) {
            $categoryData = json_decode($categoryData, true);
            $category = $this->factory->create();
            $this->updater->update($category, $categoryData);

            $violations = $this->validator->validate($category);
            if (0 !== $violations->count()) {
                throw new \Exception(sprintf('validation failed on category %s', $categoryData['code']));
            }

            $this->saver->save($category);
        }
    }
}
