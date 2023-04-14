<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Locale;

use Akeneo\Test\Acceptance\Common\ListOfCodes;
use Akeneo\Test\Common\EntityBuilder;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class LocaleContext implements Context
{
    public function __construct(
        private readonly InMemoryLocaleRepository $localeRepository,
        private readonly EntityBuilder $localeBuilder
    ) {
    }

    /**
     * @Given the following locales :localeCodes
     */
    public function theFollowingLocale(string $localeCodes)
    {
        $localeCodes = new ListOfCodes($localeCodes);

        foreach ($localeCodes->explode() as $localeCode) {
            $locale = $this->localeBuilder->build(['code' => $localeCode]);

            $this->localeRepository->save($locale);
        }
    }

    /**
     * @Then /^the locale(?:s|) "(?P<localeCodes>.*)" should be (?P<activation>activated|deactivated)$/
     */
    public function iShouldHaveLocales(string $localeCodes, string $activation)
    {
        $localeCodes = new ListOfCodes($localeCodes);

        foreach ($localeCodes->explode() as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if ('activated' === $activation) {
                Assert::assertTrue($locale->isActivated());
            } else {
                Assert::assertFalse($locale->isActivated());
            }
        }
    }
}
