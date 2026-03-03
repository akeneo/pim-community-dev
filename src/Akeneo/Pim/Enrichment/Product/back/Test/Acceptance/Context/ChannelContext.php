<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Currency;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
use Akeneo\Test\Acceptance\Channel\InMemoryChannelRepository;
use Akeneo\Test\Acceptance\Common\ListOfCodes;
use Akeneo\Test\Acceptance\Currency\InMemoryCurrencyRepository;
use Akeneo\Test\Acceptance\Locale\InMemoryLocaleRepository;
use Behat\Behat\Context\Context;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelContext implements Context
{
    public function __construct(
        private InMemoryLocaleRepository $localeRepository,
        private InMemoryCategoryRepository $categoryRepository,
        private InMemoryChannelRepository $channelRepository,
        private InMemoryCurrencyRepository $currencyRepository
    ) {
    }

    /**
     * @Given the following :channelCode channel with locales :localeCodes
     */
    public function theFollowingChannel(string $channelCode, string $localeCodes)
    {
        $localeCodes = new ListOfCodes($localeCodes);

        $masterCategory = new Category();
        $masterCategory->setCode('master');
        $this->categoryRepository->save($masterCategory);

        $currency = new Currency();
        $currency->setCode('EUR');
        $this->currencyRepository->save($currency);

        $channel = new Channel();
        $channel->setCode($channelCode);

        $channel->setCategory($masterCategory);
        $channel->addCurrency($currency);
        foreach ($localeCodes->explode() as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null === $locale) {
                $locale = new Locale();
                $locale->setCode($localeCode);
                $this->localeRepository->save($locale);
            }
            $channel->addLocale($locale);
        }
        $this->channelRepository->save($channel);
    }
}
