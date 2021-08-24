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

    private function getMediaFilesFixturesPath(): string
    {
        return $this->getFixturesPath() . '/media_files.json';
    }

    private function getProductsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/products.json';
    }

    private function getProductModelsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/product_models.json';
    }

    private function getProductAssociationsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/product_associations.json';
    }

    private function getProductModelAssociationsFixturesPath(): string
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

    private function getCategoriesFixturesPath(): string
    {
        return $this->getFixturesPath() . '/categories.json';
    }

    private function getAttributesFixturesPath(): string
    {
        return $this->getFixturesPath() . '/attributes.json';
    }

    private function getAttributeGroupsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/attribute_groups.json';
    }

    private function getAttributeOptionsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/attribute_options.json';
    }

    private function getAssociationTypesFixturesPath(): string
    {
        return $this->getFixturesPath() . '/association_types.json';
    }

    private function getChannelsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/channels.json';
    }

    private function getLocalesFixturesPath(): string
    {
        return $this->getFixturesPath() . '/locales.json';
    }

    private function getCurrenciesFixturesPath(): string
    {
        return $this->getFixturesPath() . '/currencies.json';
    }

    private function getFamiliesFixturesPath(): string
    {
        return $this->getFixturesPath() . '/families.json';
    }

    private function getFamilyVariantsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/family_variants.json';
    }
}
