<?php

declare(strict_types=1);

namespace Akeneo\Channel\Test\Integration\Query;

use Akeneo\Channel\API\Query\GetEditableLocaleCodes;
use Akeneo\Channel\Test\Integration\ChannelTestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PHPUnit\Framework\Assert;

final class GetEditableLocaleCodesIntegration extends ChannelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (FeatureHelper::isPermissionFeatureActivated()) {
            Assert::markTestSkipped('These tests are intended for CE scope only');
        }

        parent::setUp();
        $this->sqlGetEditableLocaleCodes = $this->get(GetEditableLocaleCodes::class);
        $this->loadChannelFunctionalFixtures();
    }

    /** @test */
    public function it_returns_all_activated_locale_codes_for_any_userid(): void
    {
        $expectedLocales = ['en_US', 'de_DE', 'fr_FR'];

        Assert::assertEqualsCanonicalizing(
            $expectedLocales,
            $this->sqlGetEditableLocaleCodes->forUserId(123456789)
        );

        Assert::assertEqualsCanonicalizing(
            $expectedLocales,
            $this->sqlGetEditableLocaleCodes->forUserId(987654321)
        );

        Assert::assertEqualsCanonicalizing(
            $expectedLocales,
            $this->sqlGetEditableLocaleCodes->forUserId(0)
        );
    }
}
