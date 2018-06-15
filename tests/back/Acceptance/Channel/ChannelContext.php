<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Channel;

use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
use Akeneo\Test\Acceptance\Common\ListOfCodes;
use Akeneo\Test\Acceptance\Currency\InMemoryCurrencyRepository;
use Akeneo\Test\Acceptance\Locale\InMemoryLocaleRepository;
use Akeneo\Test\Common\EntityBuilder;
use Behat\Behat\Context\Context;

final class ChannelContext implements Context
{
    /** @var InMemoryLocaleRepository */
    private $localeRepository;

    /** @var InMemoryChannelRepository */
    private $channelRepository;

    /** @var InMemoryCategoryRepository */
    private $categoryRepository;

    /** @var EntityBuilder */
    private $channelBuilder;

    /** @var EntityBuilder */
    private $categoryBuilder;

    /** @var EntityBuilder */
    private $currencyRepository;

    /** @var EntityBuilder */
    private $currencyBuilder;

    public function __construct(
        InMemoryLocaleRepository $localeRepository,
        InMemoryCategoryRepository $categoryRepository,
        InMemoryChannelRepository $channelRepository,
        InMemoryCurrencyRepository $currencyRepository,
        EntityBuilder $categoryBuilder,
        EntityBuilder $channelBuilder,
        EntityBuilder $currencyBuilder
    ) {
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categoryBuilder = $categoryBuilder;
        $this->channelBuilder = $channelBuilder;
        $this->currencyRepository = $currencyRepository;
        $this->currencyBuilder = $currencyBuilder;
    }

    /**
     * @Given the following :channelCode channel with locales :localeCodes
     */
    public function theFollowingChannel(string $channelCode, string $localeCodes)
    {
        $localeCodes = new ListOfCodes($localeCodes);

        $masterCategory = $this->categoryBuilder->build(['code' => 'master']);
        $this->categoryRepository->save($masterCategory);

        $currency = $this->currencyBuilder->build(['code' => 'EUR']);
        $this->currencyRepository->save($currency);

        $channelData = [
            'code' => $channelCode,
            'locales' => $localeCodes->explode(),
            'category_tree' => 'master',
            'currencies' => ['EUR']
        ];

        $channel = $this->channelBuilder->build($channelData);
        $this->channelRepository->save($channel);
    }

    /**
     * @Then the locale :localeCode is removed from the :channelCode channel
     */
    public function iRemoveTheLocaleFromTheChannel(string $localeCode, string $channelCode)
    {
        if (null === $channel = $this->channelRepository->findOneByIdentifier($channelCode)) {
            throw new \Exception(sprintf('Channel "%s" cannot be found', $channelCode));
        }

        if (null === $locale = $this->localeRepository->findOneByIdentifier($localeCode)) {
            throw new \Exception(sprintf('Locale "%s" cannot be found', $localeCode));
        }

        $channel->removeLocale($locale);
        $this->channelRepository->save($channel);
    }

    /**
     * @When the locale :localeCode is added to the :channelCode channel
     */
    public function iAddTheLocaleFromTheChannel($localeCode, $channelCode)
    {
        if (null === $channel = $this->channelRepository->findOneByIdentifier($channelCode)) {
            throw new \Exception(sprintf('Channel "%s" cannot be found', $channelCode));
        }

        if (null === $locale = $this->localeRepository->findOneByIdentifier($localeCode)) {
            throw new \Exception(sprintf('Locale "%s" cannot be found', $localeCode));
        }

        $channel->addLocale($locale);

        $this->channelRepository->save($channel);
    }
}
