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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use spec\PhpSpec\Exception\Fracture\InterfaceNotImplementedExceptionSpec;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class AttributesMappingContext extends PimContext
{
    use SpinCapableTrait;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param string $mainContextClass
     * @param FamilyRepositoryInterface $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        string $mainContextClass,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($mainContextClass);
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @When the attributes are mapped for the family ":familyCode" as follows:
     *
     * @param string $familyCode
     * @param TableNode $table
     */
    public function theAttributesAreMappedForTheFamilyAsFollows(string $familyCode, TableNode $table): void
    {
        $this->getNavigationContext()->iAmLoggedInAs('admin', 'admin');
        $this->getNavigationContext()->iAmOnThePage('Franklin attributes mapping');

        $this->selectFamily($familyCode);

        foreach ($this->extractAttributesMappingFromTable($table) as $targetAttributeCode => $pimAttributeCode) {
            $this->getCurrentPage()->fillAttributeMappingField(
                $targetAttributeCode,
                $this->getAttributeLabel($pimAttributeCode)
            );
        }

        $this->getCurrentPage()->save();
    }

    /**
     * @Then Franklin's attribute :franklinAttributeId should be mapped to :pimAttributeCode
     *
     * @param string $franklinAttributeId
     * @param string $pimAttributeCode
     */
    public function franklinAttributeShouldBeMappedTo(
        string $franklinAttributeId,
        string $pimAttributeCode
    ): void {
        $this->getMainContext()->getSubcontext('assertions')->assertPageNotContainsText('There are unsaved changes');
    }

    /**
     * @Then Franklin's attribute :franklinAttributeIdcolor should not be mapped
     *
     * @param string $franklinAttributeId
     */
    public function franklinAttributeShouldNotBeMapped(string $franklinAttributeId): void
    {
        $this->getMainContext()->getSubcontext('assertions')->assertPageNotContainsText('There are unsaved changes');
    }

    /**
     * @When I create the :franklinAttrType attribute :franklinAttrLabel in the family :familyCode
     */
    public function iCreateTheAttributeInTheFamily(
        string $franklinAttrType,
        string $franklinAttrLabel,
        string $familyCode
    ): void {
        $this->getNavigationContext()->iAmLoggedInAs('admin', 'admin');
        $this->getNavigationContext()->iAmOnThePage('Franklin attributes mapping');

        /** @var NodeElement $cell */
        $cell = $this->spin(
            function () use ($franklinAttrLabel) {
                $nodeElements = $this->getCurrentPage()->findAll('css', '.franklin-attribute div.label');
                foreach ($nodeElements as $nodeElement) {
                    if ($franklinAttrLabel === $nodeElement->getText()) {
                        return $nodeElement->getParent();
                    }
                }
            },
            'Could not find franklin attribute row for label "'.$franklinAttrLabel.'".'
        );

        /** @var NodeElement $button */
        $button = $this->spin(
            function () use ($cell) {
                return $cell->getParent()->find('css', 'button.create-attribute-button');
            },
            'Could not find create attribute button for "'.$franklinAttrLabel.'"'
        );

        $button->click();
    }

    /**
     * @Then the family :familyCode should have the :franklinAttrType attribute :attrCode
     */
    public function theFamilyShouldHaveTheAttribute($familyCode, $franklinAttrType, $attrCode): void
    {
        $this->spin(
            function () use ($attrCode) {
                return $this->getCurrentPage()->find('css', 'input[value="'.$attrCode.'"]');
            },
            'Could not find selected attribute for code "'.$attrCode.'".'
        );
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
    private function extractAttributesMappingFromTable(TableNode $tableNode): array
    {
        $mapping = [];
        foreach ($tableNode->getColumnsHash() as $column) {
            $franklinCode = $column['target_attribute_code'];
            $mapping[$franklinCode] = $column['pim_attribute_code'];
        }

        return $mapping;
    }

    /**
     * @param string $attributeCode
     *
     * @return string|null
     */
    private function getAttributeLabel(string $attributeCode): ?string
    {
        if ('' === $attributeCode) {
            return '';
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        $attribute->setLocale('en_US');

        return $attribute->getLabel();
    }
}
