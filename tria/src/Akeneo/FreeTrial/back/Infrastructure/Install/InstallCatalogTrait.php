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
        return __DIR__ . '/fixtures/free_trial_catalog';
    }

    private function getJobsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/jobs.yml';
    }

    private function getMediaFileFixturesPath(): string
    {
        return $this->getFixturesPath() . '/media_files.json';
    }

    private function getProductFixturesPath(): string
    {
        return $this->getFixturesPath() . '/products.json';
    }

    private function getProductModelFixturesPath(): string
    {
        return $this->getFixturesPath() . '/product_models.json';
    }

    private function getProductAssociationFixturesPath(): string
    {
        return $this->getFixturesPath() . '/product_associations.json';
    }

    private function getProductModelAssociationFixturesPath(): string
    {
        return $this->getFixturesPath() . '/product_model_associations.json';
    }

    private function getViewsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/views.csv';
    }

    private function getCategoriesCodesFixturesPath(): string
    {
        return $this->getFixturesPath() . '/categories_codes.csv';
    }

    private function getCategoryFixturesPath(): string
    {
        return $this->getFixturesPath() . '/categories.json';
    }

    private function getAttributeFixturesPath(): string
    {
        return $this->getFixturesPath() . '/attributes.json';
    }

    private function getAttributeGroupFixturesPath(): string
    {
        return $this->getFixturesPath() . '/attribute_groups.json';
    }

    private function getAttributeOptionFixturesPath(): string
    {
        return $this->getFixturesPath() . '/attribute_options.json';
    }

    private function getAssociationTypeFixturesPath(): string
    {
        return $this->getFixturesPath() . '/association_types.json';
    }

    private function getChannelFixturesPath(): string
    {
        return $this->getFixturesPath() . '/channels.json';
    }

    private function getLocaleFixturesPath(): string
    {
        return $this->getFixturesPath() . '/locales.json';
    }

    private function getCurrencyFixturesPath(): string
    {
        return $this->getFixturesPath() . '/currencies.json';
    }

    private function getFamilyFixturesPath(): string
    {
        return $this->getFixturesPath() . '/families.json';
    }

    private function getFamilyVariantFixturesPath(): string
    {
        return $this->getFixturesPath() . '/family_variants.json';
    }
}
