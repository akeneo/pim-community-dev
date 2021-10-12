<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\FixturesLoader;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Updater\LocaleUpdater;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LocaleFixturesLoader
{
    private SimpleFactoryInterface $localeFactory;
    private LocaleUpdater $localeUpdater;
    private SaverInterface $localeSaver;
    private ValidatorInterface $validator;

    public function __construct(
        SimpleFactoryInterface $localeFactory,
        LocaleUpdater $localeUpdater,
        SaverInterface $localeSaver,
        ValidatorInterface $validator
    ) {
        $this->localeFactory = $localeFactory;
        $this->localeUpdater = $localeUpdater;
        $this->localeSaver = $localeSaver;
        $this->validator = $validator;
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
}
