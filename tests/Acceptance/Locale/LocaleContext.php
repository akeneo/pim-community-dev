<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Locale;

use Akeneo\Test\Acceptance\ResourceBuilder;
use Behat\Behat\Context\Context as BehatContext;
use PHPUnit\Framework\Assert;

class LocaleContext implements BehatContext
{
    /** @var InMemoryLocaleRepository */
    private $localeRepository;

    /** @var ResourceBuilder */
    private $localeBuilder;

    public function __construct(
        InMemoryLocaleRepository $localeRepository,
        ResourceBuilder $localeBuilder
    ) {
        $this->localeRepository = $localeRepository;
        $this->localeBuilder = $localeBuilder;
    }

    /**
     * @Given /^the following locales? "([^"]*)"$/
     */
    public function theFollowingLocale(string $localeCodes)
    {
        $localeCodes = explode(',', $localeCodes);
        foreach ($localeCodes as $localeCode) {
            $localeCode = trim($localeCode);

            $locale = $this->localeBuilder->build(['code' => $localeCode]);

            $this->localeRepository->save($locale);
        }
    }

    /**
     * @Then /^I should have activated locales "([^"]*)"$/
     */
    public function iShouldHaveActivatedLocales(string $localeCodes)
    {
        foreach (explode(',', $localeCodes) as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier(trim($localeCode));
            Assert::assertTrue($locale->isActivated());
        }
    }
}
