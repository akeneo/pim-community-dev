<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Channel\Locale;

use Akeneo\Test\Acceptance\Channel\Locale\InMemoryLocaleRepository;
use Akeneo\Test\Common\Builder\EntityBuilder;
use Akeneo\Test\Common\ListOfCodes;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class LocaleContext implements Context
{
    /** @var InMemoryLocaleRepository */
    private $localeRepository;

    /** @var EntityBuilder */
    private $localeBuilder;

    public function __construct(
        InMemoryLocaleRepository $localeRepository,
        EntityBuilder $localeBuilder
    ) {
        $this->localeRepository = $localeRepository;
        $this->localeBuilder = $localeBuilder;
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
     * @Then the locales :localeCodes is activated
     */
    public function iShouldHaveActivatedLocales(string $localeCodes)
    {
        $localeCodes = new ListOfCodes($localeCodes);

        foreach ($localeCodes->explode() as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            Assert::assertTrue($locale->isActivated());
        }
    }
}
