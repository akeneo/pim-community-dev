<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Acceptance\Context;

use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use Akeneo\Test\Acceptance\Common\ListOfCodes;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class EnrichmentFollowUpContext implements Context
{
    private const IN_MEMORY_PRODUCT_TOTAL = 1259;
    private const IN_MEMORY_INCOMPLETE_PRODUCT_TOTAL = 978;

    /** @var ChannelCompleteness[] */
    private $channelCompletenesses;

    /** @var array */
    private $completenessResults = [];


    /**
     * @Given the channel :channel with only complete products
     *
     * @param string $channel
     */
    public function theChannelWithOnlyCompleteProducts(string $channel): void
    {
        $this->theChannelWithOnlyCompleteProductsForLocales($channel, 'French and English');
    }

    /**
     * @Given /^the channel? (.*) with only complete products for? (.*) locale$/
     *
     * @param string $channel
     * @param string $locales
     */
    public function theChannelWithOnlyCompleteProductsForLocales(string $channel, string $locales): void
    {
        $localeCodes = new ListOfCodes($locales);
        $locales = $localeCodes->explode();

        $localeCompletenesses = [];
        foreach ($locales as $locale) {
            $localeCompletenesses[$locale] = new LocaleCompleteness($locale, self::IN_MEMORY_PRODUCT_TOTAL);
        }

        $this->addChannelCompleteness($channel, self::IN_MEMORY_PRODUCT_TOTAL, self::IN_MEMORY_PRODUCT_TOTAL, $localeCompletenesses);
    }

    /**
     * @Given the channel :channel with some incomplete products
     *
     * @param string $channel
     */
    public function theChannelWithSomeIncompleteProducts(string $channel): void
    {
        $this->theChannelWithSomeIncompleteProductsForLocales($channel, 'French and English');
    }

    /**
     * @Given /^the channel? (.*) with some incomplete products for? (.*) locale$/
     *
     * @param string $channel
     * @param string $locales
     */
    public function theChannelWithSomeIncompleteProductsForLocales(string $channel, string $locales): void
    {
        $localeCodes = new ListOfCodes($locales);
        $locales = $localeCodes->explode();

        $localeCompletenesses = [];
        foreach ($locales as $locale) {
            $localeCompletenesses[$locale] = new LocaleCompleteness($locale, self::IN_MEMORY_INCOMPLETE_PRODUCT_TOTAL);
        }

        $this->addChannelCompleteness($channel, self::IN_MEMORY_INCOMPLETE_PRODUCT_TOTAL, self::IN_MEMORY_PRODUCT_TOTAL, $localeCompletenesses);
    }

    /**
     * @When the product manager asks for the completeness of the catalog
     */
    public function theProductManagerAskForTheCompletenessOfTheCatalog(): void
    {
        $this->completenessResults = (new CompletenessWidget($this->channelCompletenesses))->toArray();
    }

    /**
     * @Then the widget displays that the channel :channel is complete
     *
     * @param string $channel
     */
    public function theWidgetDisplaysThatTheChannelIsComplete(string $channel): void
    {
        $this->theChannelIsCompleteForTheLocales($channel, 'French and English');
    }

    /**
     * @Then /^the channel? (.*) is complete for? (.*) locale$/
     *
     * @param string $channel
     * @param string $locales
     */
    public function theChannelIsCompleteForTheLocales(string $channel, string $locales): void
    {
        $localeCodes = new ListOfCodes($locales);
        $locales = $localeCodes->explode();

        foreach ($locales as $locale) {
            Assert::eq(
                $this->completenessResults[$channel]['locales'][$locale],
                $this->completenessResults[$channel]['total']
            );
        }
    }

    /**
     * @Then the widget displays that the channel :channel is incomplete
     *
     * @param string $channel
     */
    public function theWidgetDisplaysThatTheChannelIsIncomplete(string $channel): void
    {
        $this->theChannelIsIncompleteForTheLocales($channel, 'French and English');
    }

    /**
     * @Then /^the channel? (.*) is incomplete for? (.*) locale$/
     *
     * @param string $channel
     * @param string $locales
     */
    public function theChannelIsIncompleteForTheLocales(string $channel, string $locales): void
    {
        $localeCodes = new ListOfCodes($locales);
        $locales = $localeCodes->explode();

        foreach ($locales as $locale) {
            Assert::notEq(
                $this->completenessResults[$channel]['locales'][$locale],
                $this->completenessResults[$channel]['total']
            );
        }
    }

    /**
     * @param string $channel
     * @param int $complete
     * @param int $total
     * @param LocaleCompleteness[] $localeCompletenesses
     */
    private function addChannelCompleteness(string $channel, int $complete, int $total, array $localeCompletenesses): void
    {
        if (isset($this->channelCompletenesses[$channel])) {
            $existantLocaleCompletenesses = $this->channelCompletenesses[$channel]->localeCompletenesses();
            $localeCompletenesses = array_merge($existantLocaleCompletenesses, $localeCompletenesses);
        }

        $this->channelCompletenesses[$channel] = new ChannelCompleteness($channel, $complete, $total, $localeCompletenesses);
    }
}
