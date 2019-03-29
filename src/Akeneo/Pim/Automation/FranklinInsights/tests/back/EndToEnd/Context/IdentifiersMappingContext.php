<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\EndToEnd\Context;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class IdentifiersMappingContext extends PimContext
{
    use SpinCapableTrait;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /**
     * @param string $mainContextClass
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param JobInstanceRepository $jobInstanceRepository
     */
    public function __construct(
        string $mainContextClass,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        JobInstanceRepository $jobInstanceRepository
    ) {
        parent::__construct($mainContextClass);

        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->attributeRepository = $attributeRepository;
        $this->jobInstanceRepository = $jobInstanceRepository;
    }

    /**
     * @Given an empty identifiers mapping
     */
    public function anEmptyIdentifiersMapping(): void
    {
        $this->assertIdentifiersMappingIsEmpty();
    }

    /**
     * @When the identifiers are mapped as follows:
     *
     * @param TableNode $table
     */
    public function theIdentifiersAreMappedAsFollows(TableNode $table): void
    {
        $identifiersMapping = $this->extractIdentifiersMappingFromTable($table);

        $this->getNavigationContext()->iAmLoggedInAs('admin', 'admin');
        $this->getNavigationContext()->iAmOnThePage('Franklin identifiers mapping');

        foreach ($identifiersMapping as $identifier => $attributeCode) {
            if (!empty($attributeCode)) {
                $this->getCurrentPage()->fillIdentifierMappingField(
                    $identifier,
                    $this->getAttributeLabel($attributeCode)
                );
            }
        }

        $this->getCurrentPage()->save();
    }

    /**
     * @Then the identifiers mapping should be saved as follows:
     *
     * @param TableNode $table
     *
     * @throws \Context\Spin\TimeoutException
     */
    public function theIdentifiersMappingShouldBeSavedAsFollows(TableNode $table): void
    {
        $expectedIdentifiersMapping = $this->extractIdentifiersMappingFromTable($table);

        $this->assertIdentifiersMappingPersisted($expectedIdentifiersMapping);
    }

    /**
     * @Then the products which need resubscribing should not be identified
     */
    public function theProductsWhichNeedResubscribingShouldNotBeIdentified(): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(
            JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE
        );

        Assert::isEmpty($jobInstance->getJobExecutions()->toArray());
    }

    /**
     * Asserts the identifiers mapping is empty.
     */
    private function assertIdentifiersMappingIsEmpty(): void
    {
        $persistedIdentifiersMapping = $this->identifiersMappingRepository->find();
        Assert::true($persistedIdentifiersMapping->isEmpty());
    }

    /**
     * Transforms from gherkin table:.
     *
     * | franklin_code | attribute_code |
     * | brand         | brand          |
     * | mpn           | mpn            |
     * | upc           | ean            |
     * | asin          | asin           |
     *
     * to php array with simple identifier mapping:
     *
     * franklin_code => attribute_code
     * [
     *     'brand' => 'brand',
     *     'mpn' => 'mpn',
     *     'upc' => 'ean',
     *     'asin' => 'asin',
     * ]
     *
     * @param TableNode $tableNode
     *
     * @return array
     */
    private function extractIdentifiersMappingFromTable(TableNode $tableNode): array
    {
        $identifiersMapping = array_fill_keys(IdentifiersMapping::FRANKLIN_IDENTIFIERS, null);

        foreach ($tableNode->getColumnsHash() as $column) {
            $franklinCode = $column['franklin_code'];
            if (!array_key_exists($franklinCode, $identifiersMapping)) {
                throw new \LogicException(
                    sprintf('Key "%s" is not part of the identifier mapping', $column['franklin_code'])
                );
            }
            $identifiersMapping[$franklinCode] = empty($column['attribute_code']) ? null : $column['attribute_code'];
        }

        return $identifiersMapping;
    }

    /**
     * @param string $attributeCode
     *
     * @return string
     */
    private function getAttributeLabel(string $attributeCode): string
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        $attribute->setLocale('en_US');

        return $attribute->getLabel();
    }

    /**
     * Asserts that the persisted identifiers mapping is similar to the expected one.
     *
     * Expected Mapping format is:
     * [
     *     "asin" => "pim_asin",
     *     "upc"  => null,
     * ]
     *
     * Identifiers mapping saved in Database is:
     * [
     *     "asin" => AttributeInterface::code "pim_asin",
     *     "upc"  => null,
     * ]
     *
     * @param array $expectedMappings
     *
     * @throws \Context\Spin\TimeoutException
     */
    private function assertIdentifiersMappingPersisted(array $expectedMappings): void
    {
        $this->spin(function () use ($expectedMappings): bool {
            $persistedMappings = $this->identifiersMappingRepository->find();
            if (count($expectedMappings) !== count($persistedMappings->getMapping())) {
                return false;
            }

            foreach ($expectedMappings as $expectedFranklinCode => $expectedPimCode) {
                $mappedAttributeCode = $persistedMappings->getMappedAttributeCode($expectedFranklinCode);
                if (null === $mappedAttributeCode) {
                    if (null !== $expectedPimCode) {
                        return false;
                    }
                } else {
                    if ($expectedPimCode !== (string) $mappedAttributeCode) {
                        return false;
                    }
                }
            }

            return true;
        }, 'Unable to assert persisted identifiers mapping is the one expected.');
    }
}
