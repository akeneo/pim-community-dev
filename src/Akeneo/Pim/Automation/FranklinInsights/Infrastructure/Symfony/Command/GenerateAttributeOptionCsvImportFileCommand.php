<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateAttributeOptionCsvImportFileCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'pimee:franklin-insights:get-csv-option-mapping';

    protected function configure(): void
    {
        $this->setDescription('Fetch attributes to map in Franklin and prepare a CSV attributes import content');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $headers = ['code','label-en_US','attribute','sort_order'];
        $this->printCsvRow($output, $headers);

        foreach ($this->getFamilyCodes() as $familyCode) {
            $attributesMappingResponse = $this->getAttributesMappingByFamily($familyCode);

            $attributeCodes = [];
            foreach ($attributesMappingResponse as $attributeMapping) {
                if ($attributeMapping->getPimAttributeCode() !== null) {
                    $attributeCodes[$attributeMapping->getTargetAttributeCode()] = $attributeMapping->getPimAttributeCode();
                }
            }

            $attributeTypesByCode = $this->getContainer()->get('pim_catalog.repository.attribute')->getAttributeTypeByCodes(array_values($attributeCodes));

            $validPimAttributeCodes = array_keys(array_filter($attributeTypesByCode, function (string $type) {
                return $type === AttributeTypes::OPTION_MULTI_SELECT || $type === AttributeTypes::OPTION_SIMPLE_SELECT;
            }));

            foreach ($validPimAttributeCodes as $pimAttributeCode) {
                $franklinAttributeCode = array_search($pimAttributeCode, $attributeCodes);
                $optionsMapping = $this->getAttributeOptionsMapping($familyCode, $franklinAttributeCode);

                foreach ($optionsMapping->mapping() as $optionMapping) {
                    if ($optionMapping->status() === AttributeOptionMapping::STATUS_INACTIVE) {
                        $csvRow = array_fill_keys($headers, 0);
                        $csvRow['code'] = sprintf('%s_%s_%s', $familyCode, $franklinAttributeCode, $optionMapping->franklinAttributeOptionLabel());
                        $csvRow['label-en_US'] = $optionMapping->franklinAttributeOptionLabel();
                        $csvRow['attribute'] = $pimAttributeCode;
                        $csvRow['sort_order'] = 0;

                        $this->printCsvRow($output, $csvRow);
                    }
                }
            }

//            var_dump($attributesMappingResponse);
        }
    }

    private function printCsvRow(OutputInterface $output, array $csvRow)
    {
        $output->writeln(implode(';', $csvRow));
    }

    private function getAttributesMappingByFamily(string $familyCode): AttributesMappingResponse
    {
        return $this
            ->getContainer()
            ->get('akeneo.pim.automation.franklin_insights.handler.get_attributes_mapping_by_family')
            ->handle(new GetAttributesMappingByFamilyQuery($familyCode));
    }

    private function getAttributeOptionsMapping(string $familyCode, string $franklinAttributeId): AttributeOptionsMapping
    {
        return $this
            ->getContainer()
            ->get('akeneo.pim.automation.franklin_insights.handler.get_attribute_options_mapping_by_family_and_attribute')
            ->handle(new GetAttributeOptionsMappingQuery(new FamilyCode($familyCode), new FranklinAttributeId($franklinAttributeId)));
    }

    private function getFamilyCodes()
    {
        $sqlQuery = <<<SQL
SELECT f.code FROM pimee_franklin_insights_subscription ps 
INNER JOIN pim_catalog_product p ON p.id = ps.product_id 
INNER JOIN pim_catalog_family f ON f.id = p.family_id 
GROUP BY f.code
SQL;

        /** @var Connection $dbal */
        $dbal = $this->getContainer()->get('database_connection');
        $fetchedArray = $dbal->fetchAll($sqlQuery);

        $familyCodes = [];
        foreach ($fetchedArray as $fetchFamily) {
            $familyCodes[] = $fetchFamily['code'];
        }

        return $familyCodes;
    }
}
