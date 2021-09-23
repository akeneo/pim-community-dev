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

namespace Akeneo\FreeTrial\Infrastructure\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;

trait InstallCatalogTrait
{
    private function isFreeTrialCatalogInstallation(InstallerEvent $installerEvent): bool
    {
        $installedCatalog = $installerEvent->getArgument('catalog');

        return is_string($installedCatalog) && strpos($installedCatalog, 'free_trial_catalog') !== false;
    }

    private function getFixturesPath(): string
    {
        return __DIR__ . '/../Symfony/Resources/fixtures/free_trial_catalog';
    }

    private function getExtractedFixturesPath(): string
    {
        return $this->getFixturesPath() . '/extracted';
    }

    private function getJobFixturesPath(): string
    {
        return $this->getFixturesPath() . '/jobs.yml';
    }

    private function getMediaFileFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/media_files.json';
    }

    private function getProductFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/products.json';
    }

    private function getProductModelFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/product_models.json';
    }

    private function getProductAssociationFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/product_associations.json';
    }

    private function getProductModelAssociationFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/product_model_associations.json';
    }

    private function getViewFixturesPath(): string
    {
        return $this->getFixturesPath() . '/views.csv';
    }

    private function getCategoryCodeFixturesPath(): string
    {
        return $this->getFixturesPath() . '/categories_codes.csv';
    }

    private function getCategoryFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/categories.json';
    }

    private function getAttributeFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/attributes.json';
    }

    private function getAttributeGroupFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/attribute_groups.json';
    }

    private function getAttributeOptionFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/attribute_options.json';
    }

    private function getAssociationTypeFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/association_types.json';
    }

    private function getChannelFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/channels.json';
    }

    private function getLocaleFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/locales.json';
    }

    private function getCurrencyFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/currencies.json';
    }

    private function getFamilyFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/families.json';
    }

    private function getFamilyVariantFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/family_variants.json';
    }

    private function getMeasurementFamilyFixturesPath(): string
    {
        return $this->getExtractedFixturesPath() . '/measurement_families.json';
    }

    private function getConnectionFixturesPath(): string
    {
        return $this->getFixturesPath() . '/connections.json';
    }

    private function getConnectionImageFixturesPath(): string
    {
        return $this->getFixturesPath() . '/images';
    }
}
