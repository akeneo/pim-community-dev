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

final class InstallChannelsSubscriber implements EventSubscriberInterface
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
                InstallerEvents::POST_LOAD_FIXTURE => 'installChannels',
            ];
        }

    public function installChannels(InstallerEvent $installerEvent): void
    {
        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        if ('fixtures_channel_csv' !== $installerEvent->getSubject()) {
            return;
        }

        $channelsFile = fopen($this->getChannelsFixturesPath(), 'r');

        while ($channelData = fgets($channelsFile)) {
            $channelData = json_decode($channelData, true);
            $channel = $this->factory->create();
            $this->updater->update($channel, $channelData);

            $violations = $this->validator->validate($channel);
            if (0 !== $violations->count()) {
                throw new \Exception(sprintf('validation failed on channel %s', $channelData['code']));
            }

            $this->saver->save($channel);
        }
    }
}
