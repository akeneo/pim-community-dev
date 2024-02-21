<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeAsLabel;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeUsedAsAxis;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Tries to update family attributes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateFamilyIntegration extends TestCase
{
    public function testRemovingSimpleAttributeFromFamilyIsAllowed()
    {
        $violations = $this->removeAttributeFromFamily('shoes', ['material']);
        $this->assertEquals(0, $violations->count());
    }

    public function testRemovingAttributeUsedAsLabelIsNotAllowed()
    {
        $violations = $this->removeAttributeFromFamily('shoes', ['variation_name']);
        $this->assertEquals(1, $violations->count());
        $violation = $violations->getIterator()->current();
        $this->assertEquals(FamilyAttributeAsLabel::class, get_class($violation->getConstraint()));
        $this->assertEquals(
            'Property "attribute_as_label" must belong to the family',
            $violation->getMessage()
        );
        $this->assertEquals('attribute_as_label', $violation->getPropertyPath());
    }

    public function testRemovingAxisAttributeOfAFamilyVariantFromFamilyIsNotAllowed()
    {
        $violations = $this->removeAttributeFromFamily('shoes', ['size']);
        $this->assertEquals(1, $violations->count());
        $violation = $violations->getIterator()->current();
        $this->assertEquals(FamilyAttributeUsedAsAxis::class, get_class($violation->getConstraint()));
        $this->assertEquals(
            'Attribute "size" is an axis in "shoes_size_color" family variant. It must belong to the family.',
            $violation->getMessage()
        );
        $this->assertEquals('attributes', $violation->getPropertyPath());
    }

    public function testRemovingAttributeFromAFamilyAlsoRemovesItFromTheFamilyVariants()
    {
        $errors = $this->removeAttributeFromFamily('shoes', ['weight']);
        $this->assertEquals(0, $errors->count());
        $this->assertAttributeMissingFromFamilyVariants('weight', 'shoes');
    }

    public function testRemovingMultipleAttributesFromAFamilyAlsoRemovesItFromTheFamilyVariants()
    {
        $errors = $this->removeAttributeFromFamily('shoes', ['weight', 'composition']);
        $this->assertEquals(0, $errors->count());
        $this->assertAttributeMissingFromFamilyVariants('weight', 'shoes');
        $this->assertAttributeMissingFromFamilyVariants('composition', 'shoes');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param string $familyCode
     * @param array  $attributeCodes
     *
     * @return ConstraintViolationListInterface
     */
    private function removeAttributeFromFamily(
        string $familyCode,
        array $attributeCodes
    ): ConstraintViolationListInterface {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($familyCode);
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode);
            $family->removeAttribute($attribute);
        }

        $violations = $this->get('validator')->validate($family);

        if ($violations->count() === 0) {
            $this->get('pim_catalog.saver.family')->save($family);
        }

        return $violations;
    }

    /**
     * Asserts that the given attribute code does not belong to any variant attribute set related to the given family
     * code
     *
     * @param string $attributeCode
     * @param string $familyCode
     */
    private function assertAttributeMissingFromFamilyVariants(string $attributeCode, string $familyCode): void
    {
        $this->get('doctrine.orm.default_entity_manager')->clear();
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier(
            $familyCode
        );
        foreach ($family->getFamilyVariants() as $familyVariant) {
            $familyVariantAttributeCodes = $this->getVariantFamilyAttributeCodes($familyVariant);
            $isAttributeFound = in_array($attributeCode, $familyVariantAttributeCodes);
            $this->assertFalse(
                $isAttributeFound,
                sprintf(
                    'Attribute "%s" found in an attribute set of the family variant "%s"',
                    $attributeCode,
                    $familyVariant->getCode()
                )
            );
        }
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return array
     */
    private function getVariantFamilyAttributeCodes(FamilyVariantInterface $familyVariant): array
    {
        return $familyVariant->getAttributes()->map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        })->toArray();
    }
}
