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

namespace Akeneo\FreeTrial\Infrastructure\Install\Command\Extractor;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Symfony\Component\Console\Style\StyleInterface;

final class ExtractStructure
{
    use InstallCatalogTrait;

    private AkeneoPimClientInterface $apiClient;

    private StyleInterface $io;

    public function __construct(AkeneoPimClientInterface $apiClient, StyleInterface $io)
    {
        $this->apiClient = $apiClient;
        $this->io = $io;
    }

    public function __invoke(): void
    {
        $this->extractAttributeGroups();
        $this->extractAttributes();
        $this->extractAssociationTypes();
        $this->extractCategories();
        $this->extractChannels();
        $this->extractLocales();
        $this->extractCurrencies();
        $this->extractFamilies();
    }

    private function extractEntities(\Iterator $entities, string $targetFilePath, ?callable $cleanData = null): int
    {
        $count = 0;
        foreach ($entities as $entity) {
            unset($entity['_links']);
            if (null !== $cleanData) {
                $entity = $cleanData($entity);
            }
            file_put_contents($targetFilePath, json_encode($entity) . PHP_EOL, FILE_APPEND);
            $count++;
        }

        return $count;
    }

    private function extractAttributes(): void
    {
        $this->io->section('Extract attributes and attribute options');

        $attributeApi = $this->apiClient->getAttributeApi();
        $countAttributes = 0;
        $countAttributeOptions = 0;

        file_put_contents($this->getAttributesFixturesPath(), '');
        file_put_contents($this->getAttributeOptionsFixturesPath(), '');

        $this->io->progressStart($attributeApi->listPerPage(1, true)->getCount());

        foreach ($attributeApi->all() as $attribute) {
            unset($attribute['_links']);
            unset($attribute['group_labels']);
            file_put_contents($this->getAttributesFixturesPath(), json_encode($attribute) . PHP_EOL, FILE_APPEND);
            $countAttributes++;

            if (in_array($attribute['type'], ['pim_catalog_simpleselect', 'pim_catalog_multiselect'])) {
                $countAttributeOptions += $this->extractEntities(
                    $this->apiClient->getAttributeOptionApi()->all($attribute['code']),
                    $this->getAttributeOptionsFixturesPath()
                );
            }

            $this->io->progressAdvance(1);
        }

        $this->io->progressFinish();

        $this->io->text(sprintf('%d attributes and %d attribute options extracted', $countAttributes, $countAttributeOptions));
    }

    private function extractAttributeGroups(): void
    {
        $this->io->section('Extract attribute groups');

        file_put_contents($this->getAttributeGroupsFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getAttributeGroupApi()->all(),
            $this->getAttributeGroupsFixturesPath(),
            function (array $attributeGroup) {
                unset($attributeGroup['attributes']);
                return $attributeGroup;
            }
        );

        $this->io->text(sprintf('%d attribute groups extracted', $count));
    }

    private function extractAssociationTypes(): void
    {
        $this->io->section('Extract association types');

        file_put_contents($this->getAssociationTypesFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getAssociationTypeApi()->all(),
            $this->getAssociationTypesFixturesPath()
        );

        $this->io->text(sprintf('%d association types extracted', $count));
    }

    private function extractCategories(): void
    {
        $this->io->section('Extract categories');

        file_put_contents($this->getCategoriesFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getCategoryApi()->all(),
            $this->getCategoriesFixturesPath(),
            function (array $category) {
                unset($category['updated']);
                return $category;
            }
        );

        $this->io->text(sprintf('%d categories extracted', $count));
    }

    private function extractChannels(): void
    {
        $this->io->section('Extract channels');

        file_put_contents($this->getChannelsFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getChannelApi()->all(),
            $this->getChannelsFixturesPath()
        );

        $this->io->text(sprintf('%d channels extracted', $count));
    }

    private function extractLocales(): void
    {
        $this->io->section('Extract locales');

        file_put_contents($this->getLocalesFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getLocaleApi()->all(),
            $this->getLocalesFixturesPath(),
            function (array $locale) {
                unset($locale['enabled']);
                return $locale;
            }
        );

        $this->io->text(sprintf('%d locales extracted', $count));
    }

    private function extractCurrencies(): void
    {
        $this->io->section('Extract currencies');

        file_put_contents($this->getCurrenciesFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getCurrencyApi()->all(),
            $this->getCurrenciesFixturesPath()
        );

        $this->io->text(sprintf('%d currencies extracted', $count));
    }

    private function extractFamilies(): void
    {
        $this->io->section('Extract families and family variants');

        $familyApi = $this->apiClient->getFamilyApi();
        $countFamilies = 0;
        $countFamilyVariants = 0;

        file_put_contents($this->getFamiliesFixturesPath(), '');
        file_put_contents($this->getFamilyVariantsFixturesPath(), '');

        $this->io->progressStart($familyApi->listPerPage(1, true)->getCount());

        foreach ($familyApi->all() as $family) {
            unset($family['_links']);
            file_put_contents($this->getFamiliesFixturesPath(), json_encode($family) . PHP_EOL, FILE_APPEND);
            $countFamilies++;

            $countFamilyVariants += $this->extractEntities(
                $this->apiClient->getFamilyVariantApi()->all($family['code']),
                $this->getFamilyVariantsFixturesPath()
            );

            $this->io->progressAdvance(1);
        }

        $this->io->progressFinish();

        $this->io->text(sprintf('%d families and %d family variants extracted', $countFamilies, $countFamilyVariants));
    }
}
