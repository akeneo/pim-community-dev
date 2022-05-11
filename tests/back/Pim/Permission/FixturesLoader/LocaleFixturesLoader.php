<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\FixturesLoader;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Updater\LocaleUpdater;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LocaleFixturesLoader
{
    private SimpleFactoryInterface $localeFactory;
    private LocaleUpdater $localeUpdater;
    private SaverInterface $localeSaver;
    private ValidatorInterface $validator;
    private LocaleRepositoryInterface $localeRepository;
    private ChannelRepositoryInterface $channelRepository;

    public function __construct(
        SimpleFactoryInterface $localeFactory,
        LocaleUpdater $localeUpdater,
        SaverInterface $localeSaver,
        ValidatorInterface $validator,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->localeFactory = $localeFactory;
        $this->localeUpdater = $localeUpdater;
        $this->localeSaver = $localeSaver;
        $this->validator = $validator;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
    }

    public function createLocale(array $data, ?ChannelInterface $channel = null): LocaleInterface
    {
        /** @var LocaleInterface $locale */
        $locale = $this->localeFactory->create();
        $this->localeUpdater->update($locale, $data);

        if (null !== $channel) {
            $locale->addChannel($channel);
        }

        $this->validator->validate($locale);
        $this->localeSaver->save($locale);

        return $locale;
    }

    public function activateLocalesOnChannel(array $localeCodes, string $channelIdentifier): void
    {
        $channel = $this->channelRepository->findOneByIdentifier($channelIdentifier);

        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            $locale->addChannel($channel);

            $this->validator->validate($locale);
            $this->localeSaver->save($locale);
        }
    }
}
