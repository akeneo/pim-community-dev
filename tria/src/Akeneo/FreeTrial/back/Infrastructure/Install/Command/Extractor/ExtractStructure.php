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
use Symfony\Component\Console\Output\OutputInterface;

final class ExtractStructure
{
    use InstallCatalogTrait;

    private AkeneoPimClientInterface $apiClient;

    private OutputInterface $output;

    public function __construct(AkeneoPimClientInterface $apiClient, OutputInterface $output)
    {
        $this->apiClient = $apiClient;
        $this->output = $output;
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
        $this->extractMeasurementFamilies();
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
        $this->output->write('Extract attributes and attribute options... ');

        $attributeApi = $this->apiClient->getAttributeApi();
        $countAttributes = 0;
        $countAttributeOptions = 0;

        file_put_contents($this->getAttributeFixturesPath(), '');
        file_put_contents($this->getAttributeOptionFixturesPath(), '');

        foreach ($attributeApi->all(pageSize: 100) as $attribute) {
            unset($attribute['_links']);
            unset($attribute['group_labels']);
            file_put_contents($this->getAttributeFixturesPath(), json_encode($attribute) . PHP_EOL, FILE_APPEND);
            $countAttributes++;

            if (in_array($attribute['type'], ['pim_catalog_simpleselect', 'pim_catalog_multiselect'])) {
                $countAttributeOptions += $this->extractEntities(
                    $this->apiClient->getAttributeOptionApi()->all(attributeCode: $attribute['code'], pageSize: 100),
                    $this->getAttributeOptionFixturesPath()
                );
            }
        }

        $this->output->writeln(
            sprintf('%d attributes and %d attribute options extracted', $countAttributes, $countAttributeOptions)
        );
    }

    private function extractAttributeGroups(): void
    {
        $this->output->write('Extract attribute groups... ');

        file_put_contents($this->getAttributeGroupFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getAttributeGroupApi()->all(pageSize: 100),
            $this->getAttributeGroupFixturesPath(),
            function (array $attributeGroup) {
                unset($attributeGroup['attributes']);
                return $attributeGroup;
            }
        );

        $this->output->writeln(sprintf('%d attribute groups extracted', $count));
    }

    private function extractAssociationTypes(): void
    {
        $this->output->write('Extract association types... ');

        file_put_contents($this->getAssociationTypeFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getAssociationTypeApi()->all(pageSize: 100),
            $this->getAssociationTypeFixturesPath()
        );

        $this->output->writeln(sprintf('%d association types extracted', $count));
    }

    private function extractCategories(): void
    {
        $this->output->write('Extract categories... ');

        file_put_contents($this->getCategoryFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getCategoryApi()->all(pageSize: 100),
            $this->getCategoryFixturesPath(),
            function (array $category) {
                unset($category['updated']);
                return $category;
            }
        );

        $this->output->writeln(sprintf('%d categories extracted', $count));
    }

    private function extractChannels(): void
    {
        $this->output->write('Extract channels... ');

        file_put_contents($this->getChannelFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getChannelApi()->all(pageSize: 100),
            $this->getChannelFixturesPath()
        );

        $this->output->writeln(sprintf('%d channels extracted', $count));
    }

    private function extractLocales(): void
    {
        $this->output->write('Extract locales... ');

        file_put_contents($this->getLocaleFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getLocaleApi()->all(pageSize: 100),
            $this->getLocaleFixturesPath(),
            function (array $locale) {
                unset($locale['enabled']);
                return $locale;
            }
        );

        $this->output->writeln(sprintf('%d locales extracted', $count));
    }

    private function extractCurrencies(): void
    {
        $this->output->write('Extract currencies... ');

        file_put_contents($this->getCurrencyFixturesPath(), '');

        $count = $this->extractEntities(
            $this->apiClient->getCurrencyApi()->all(pageSize: 100),
            $this->getCurrencyFixturesPath()
        );

        $this->output->writeln(sprintf('%d currencies extracted', $count));
    }

    private function extractFamilies(): void
    {
        $this->output->write('Extract families and family variants... ');

        $familyApi = $this->apiClient->getFamilyApi();
        $countFamilies = 0;
        $countFamilyVariants = 0;

        file_put_contents($this->getFamilyFixturesPath(), '');
        file_put_contents($this->getFamilyVariantFixturesPath(), '');

        foreach ($familyApi->all(pageSize: 100) as $family) {
            unset($family['_links']);
            file_put_contents($this->getFamilyFixturesPath(), json_encode($family) . PHP_EOL, FILE_APPEND);
            $countFamilies++;

            $countFamilyVariants += $this->extractFamilyVariants($family['code']);
        }

        $this->output->writeln(
            sprintf('%d families and %d family variants extracted', $countFamilies, $countFamilyVariants)
        );
    }

    private function extractFamilyVariants(string $family): int
    {
        $count = 0;
        $familyVariants = $this->apiClient->getFamilyVariantApi()->all(familyCode: $family, pageSize: 100);

        foreach ($familyVariants as $familyVariant) {
            unset($familyVariant['_links']);
            $familyVariant['family'] = $family;
            file_put_contents($this->getFamilyVariantFixturesPath(), json_encode($familyVariant) . PHP_EOL, FILE_APPEND);
            $count++;
        }

        return $count;
    }

    private function extractMeasurementFamilies(): void
    {
        $this->output->write('Extract measurement families... ');

        file_put_contents($this->getMeasurementFamilyFixturesPath(), '');

        $count = 0;
        foreach ($this->apiClient->getMeasurementFamilyApi()->all() as $measurement) {
            file_put_contents($this->getMeasurementFamilyFixturesPath(), json_encode($measurement) . PHP_EOL, FILE_APPEND);
            $count++;
        }

        $this->output->writeln(sprintf('%d measurements extracted', $count));
    }
}
