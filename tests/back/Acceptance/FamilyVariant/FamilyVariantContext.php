<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\FamilyVariant;

use Akeneo\Test\Acceptance\Attribute\AttributeContext;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\Common\ListOfCodes;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;
use Akeneo\Test\Common\Builder\EntityBuilder;
use Behat\Behat\Context\Context;
use Pim\Component\Catalog\AttributeTypes;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantContext implements Context
{
    /** @var InMemoryFamilyVariantRepository */
    private $familyVariantRepository;

    /** @var InMemoryFamilyRepository */
    private $familyRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var EntityBuilder */
    private $familyVariantBuilder;

    /** @var EntityBuilder */
    private $familyBuilder;

    /** @var EntityBuilder */
    private $attributeBuilder;

    /**
     * @param InMemoryFamilyVariantRepository $familyVariantRepository
     * @param InMemoryFamilyRepository        $familyRepository
     * @param InMemoryAttributeRepository     $attributeRepository
     * @param EntityBuilder                   $familyVariantBuilder
     * @param EntityBuilder                   $familyBuilder
     * @param EntityBuilder                   $attributeBuilder
     */
    public function __construct(
        InMemoryFamilyVariantRepository $familyVariantRepository,
        InMemoryFamilyRepository $familyRepository,
        InMemoryAttributeRepository $attributeRepository,
        EntityBuilder $familyVariantBuilder,
        EntityBuilder $familyBuilder,
        EntityBuilder $attributeBuilder
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->familyVariantBuilder = $familyVariantBuilder;
        $this->familyBuilder = $familyBuilder;
        $this->attributeBuilder = $attributeBuilder;
    }

    /**
     * @param string $variantFamilyCode
     * @param string $familyCode
     * @param string $axisCodes
     *
     * @Given /^a one level family variant ([^"]*) from family ([^"]*) with axes (.*)$/
     */
    public function createOneLevelFamilyVariant(string $variantFamilyCode, string $familyCode, string $axisCodes)
    {
        $identifierAttribute = $this->attributeRepository->getIdentifier();
        if (null === $identifierAttribute) {
            $this->createIdentifierAttribute();
        }

        $listOfAxisCodes = new ListOfCodes($axisCodes);

        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        if (null === $family) {
            $family = $this->familyBuilder->build([
                'code' => $familyCode,
                'attributes' => array_merge(
                    $listOfAxisCodes->explode(', '),
                    [$identifierAttribute->getCode()]
                ),
            ]);

            $this->familyRepository->save($family);
        }

        $familyVariant = $this->familyVariantBuilder->build([
            'code' => $variantFamilyCode,
            'family' => $familyCode,
            'variant_attribute_sets' => [
                'level' => 1,
                'axes' => [$listOfAxisCodes->explode(', ')],
            ],
        ]);

        $this->familyVariantRepository->save($familyVariant);
    }

    /**
     * @param string $variantFamilyCode
     * @param string $familyCode
     * @param string $level1AxisCodes
     * @param string $level2AxisCodes
     *
     *
     * @Given /^a two levels family variant tshirts_variant_2 from family tshirts with first level axis color and second level axis size$/
     */
    public function createTwoLevelsFamilyVariant(
        string $variantFamilyCode,
        string $familyCode,
        string $level1AxisCodes,
        string $level2AxisCodes
    ) {
        //
    }

    private function createIdentifierAttribute()
    {
        $identifierAttribute = $this->attributeBuilder->build([
            'code' => 'sku',
            'type' => AttributeTypes::IDENTIFIER,
            'group' => AttributeContext::DEFAULT_ATTRIBUTE_GROUP,
            'useable_as_grid_filter' => true,
        ]);

        $this->attributeRepository->save($identifierAttribute);
    }
}
