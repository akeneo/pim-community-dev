<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountLocalesIntegration extends QueryTestCase
{
    public function testGetCountOfActivatedLocales()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_locales');
        $this->activateLocale('fr_FR');
        $this->activateLocale('en_US');
        $this->activateLocale('de_DE');

        $volume = $query->fetch();

        Assert::assertEquals(3, $volume->getVolume());
        Assert::assertEquals('count_locales', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param string $codeLocale
     */
    private function activateLocale(string $codeLocale): void
    {
        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier($codeLocale);
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');

        $locale->addChannel($channel);

        $errors = $this->get('validator')->validate($locale);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.locale')->save($locale);
    }
}
