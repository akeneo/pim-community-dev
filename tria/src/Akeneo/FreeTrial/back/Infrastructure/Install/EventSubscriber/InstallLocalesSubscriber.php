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

final class InstallLocalesSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    private SimpleFactoryInterface $factory;

    private ValidatorInterface $validator;

    public function __construct(
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        SimpleFactoryInterface $factory,
        ValidatorInterface $validator
    ) {
        $this->updater = $updater;
        $this->saver = $saver;
        $this->factory = $factory;
        $this->validator = $validator;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURE => 'installLocales',
        ];
    }

    public function installLocales(InstallerEvent $installerEvent): void
    {
        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        if ('fixtures_locale_csv' !== $installerEvent->getSubject()) {
            return;
        }

        $localesFile = fopen($this->getLocalesFixturesPath(), 'r');

        while ($localeData = fgets($localesFile)) {
            $localeData = json_decode($localeData, true);
            $locale = $this->factory->create();
            $this->updater->update($locale, $localeData);

            $violations = $this->validator->validate($locale);
            if (0 !== $violations->count()) {
                throw new \Exception(sprintf('validation failed on locale %s', $localeData['code']));
            }

            $this->saver->save($locale);
        }
    }
}
