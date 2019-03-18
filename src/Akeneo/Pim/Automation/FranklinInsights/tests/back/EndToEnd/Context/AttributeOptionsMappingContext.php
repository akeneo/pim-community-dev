<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\EndToEnd\Context;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository\AttributeOptionRepositoryInterface;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMappingContext extends PimContext
{
    use SpinCapableTrait;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeOptionRepositoryInterface */
    private $attributeOptionRepository;

    public function __construct(string $mainContextClass, FamilyRepositoryInterface $familyRepository, AttributeOptionRepositoryInterface $attributeOptionRepository)
    {
        parent::__construct($mainContextClass);
        $this->familyRepository = $familyRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * @When the Franklin :franklinAttrId options are mapped to the PIM :catalogAttrCode options for the family :familyCode as follows:
     *
     * @param string $familyCode
     * @param TableNode $table
     * @param mixed $catalogAttrCode
     */
    public function theAttributeOptionsAreMappedAsFollows($catalogAttrCode, $familyCode, TableNode $table): void
    {
        $this->getNavigationContext()->iAmLoggedInAs('admin', 'admin');
        $this->getNavigationContext()->iAmOnThePage('Franklin attributes mapping');

        $this->selectFamily($familyCode);

        $this->spin(function () use ($catalogAttrCode) {
            $optionMappingButton = $this->getCurrentPage()->find('css', sprintf('.option-mapping[data-franklin-attribute-code="%s"]', $catalogAttrCode));
            if (null === $optionMappingButton) {
                return false;
            }
            $optionMappingButton->click();

            return true;
        }, sprintf('Could not find attribute "%s" option mapping button', $catalogAttrCode));

        $options = $this->extractAttributeOptionsMappingFromTable($table);
        foreach ($options as $targetOptionCode => $pimOptionCode) {
            $this->getCurrentPage()->fillAttributeMappingField(
                $targetOptionCode,
                $this->getAttributeOptionLabel($catalogAttrCode, $pimOptionCode)
            );
        }

        $this->spin(function () {
            $saveButton = $this->getCurrentPage()->find('css', '.modal .AknButton--apply.save');
            if (null === $saveButton) {
                return false;
            }
            $saveButton->click();

            return true;
        }, 'Could not find save button');
    }

    /**
     * @Then Franklin option :franklinOptionId should be mapped to :pimOptionCode
     */
    public function franklinAttributeShouldBeMappedTo(): void
    {
        $this->getMainContext()->getSubcontext('assertions')->assertPageNotContainsText('There are unsaved changes');
    }

    /**
     * @Then Franklin option :franklinOptionId should not be mapped
     */
    public function franklinAttributeShouldNotBeMapped(): void
    {
        $this->getMainContext()->getSubcontext('assertions')->assertPageNotContainsText('There are unsaved changes');
    }

    /**
     * @param string $familyCode
     *
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     * @throws \Context\Spin\TimeoutException
     */
    private function selectFamily(string $familyCode): void
    {
        $family = $this->familyRepository->findOneByIdentifier(new FamilyCode($familyCode));

        $this->spin(
            function () {
                return $this->getCurrentPage()->findField('Family');
            },
            'Could not find family select input'
        );

        $this->getCurrentPage()->fillField('Family', $family->getLabel('en_US'));
    }

    /**
     * @param TableNode $tableNode
     *
     * @return array
     */
    private function extractAttributeOptionsMappingFromTable(TableNode $tableNode): array
    {
        $mapping = [];
        foreach ($tableNode->getColumnsHash() as $column) {
            $franklinCode = $column['franklin_attribute_option_id'];
            $mapping[$franklinCode] = $column['catalog_attribute_option_code'];
        }

        return $mapping;
    }

    /**
     * @param string $optionCode
     *
     * @return string|null
     */
    private function getAttributeOptionLabel(string $attributeCode, string $optionCode): ?string
    {
        if ('' === $optionCode) {
            return '';
        }

        $attributeOption = $this->attributeOptionRepository->findOneByIdentifier(
            new AttributeCode($attributeCode),
            $optionCode
        );
        $attributeOption->setLocale('en_US');

        return $attributeOption->getTranslation()->getLabel();
    }
}
