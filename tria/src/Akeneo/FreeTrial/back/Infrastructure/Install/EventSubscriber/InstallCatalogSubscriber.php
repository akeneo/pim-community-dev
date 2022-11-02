<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\EventSubscriber;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\FreeTrial\Infrastructure\Install\Installer\FixtureInstallerRegistry;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class InstallCatalogSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private FixtureInstallerRegistry $installerRegistry;

    public function __construct(FixtureInstallerRegistry $installerRegistry)
    {
        $this->installerRegistry = $installerRegistry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURES => 'onPreLoadFixtures',
            InstallerEvents::POST_LOAD_FIXTURES => 'onPostLoadFixtures',
            InstallerEvents::PRE_LOAD_FIXTURE => 'onPreLoadFixture',
            InstallerEvents::POST_LOAD_FIXTURE => 'onPostLoadFixture',
        ];
    }

    public function onPreLoadFixtures(InstallerEvent $installerEvent): void
    {
        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        $this->installFixture('measurement_family');
        $this->installFixture('media_file');
    }

    public function onPostLoadFixtures(InstallerEvent $installerEvent): void
    {
        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        $this->installFixture('connection');
        $this->installFixture('connection_data_flows');
        $this->installFixture('view');
        $this->installFixture('dqi_dashboard');
    }

    public function onPreLoadFixture(InstallerEvent $installerEvent): void
    {
        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        switch ($installerEvent->getSubject()) {
            case 'fixtures_product_csv':
                $this->installFixture('product');
                break;
            case 'fixtures_product_model_csv':
                $this->installFixture('product_model');
                break;
        }
    }

    public function onPostLoadFixture(InstallerEvent $installerEvent): void
    {
        if (!$this->isFreeTrialCatalogInstallation($installerEvent)) {
            return;
        }

        switch ($installerEvent->getSubject()) {
            case 'fixtures_category_csv':
                $this->installFixture('category');
                break;
            case 'fixtures_channel_csv':
                $this->installFixture('channel');
                break;
            case 'fixtures_currency_csv':
                $this->installFixture('currency');
                break;
            case 'fixtures_locale_csv':
                $this->installFixture('locale');
                break;
            case 'fixtures_association_type_csv':
                $this->installFixture('association_type');
                break;
            case 'fixtures_attribute_csv':
                $this->installFixture('attribute');
                break;
            case 'fixtures_attribute_group_csv':
                $this->installFixture('attribute_group');
                break;
            case 'fixtures_attribute_options_csv':
                $this->installFixture('attribute_option');
                break;
            case 'fixtures_family_csv':
                $this->installFixture('family');
                break;
            case 'fixtures_family_variant_csv':
                $this->installFixture('family_variant');
                break;
            case 'fixtures_product_csv':
                $this->installFixture('product_association');
                $this->installFixture('product_evaluation');
                break;
            case 'fixtures_product_model_csv':
                $this->installFixture('product_model_association');
                $this->installFixture('product_model_evaluation');
                break;
        }
    }

    private function installFixture(string $fixtureName): void
    {
        $installer = $this->installerRegistry->getInstaller($fixtureName);
        $installer->install();
    }
}
