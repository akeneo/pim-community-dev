<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeSearchableRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class AttributeSearchableRepositoryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_excludes_family()
    {
        $this->createFamily('familyA');
        $this->createFamily('familyB');
        $this->addAttributeToFamilies('attr1', ['familyA']);
        $this->addAttributeToFamilies('attr2', ['familyB']);
        $this->addAttributeToFamilies('attr3', ['familyA', 'familyB']);
        $this->addAttributeToFamilies('attr4', []);

        // Without exclude
        $result = $this->getAttributeSearchableRepository()->findBySearch('');
        $expected = ['attr1', 'attr2', 'attr3', 'attr4', 'sku'];
        $this->validateSameAttributeCodes($result, $expected);

        // With exclude
        $result = $this->getAttributeSearchableRepository()->findBySearch('', ['excluded_family' => 'familyA']);
        $expected = ['attr2', 'attr4'];
        $this->validateSameAttributeCodes($result, $expected);
    }

    private function getAttributeSearchableRepository(): AttributeSearchableRepository
    {
        return $this->get('pim_enrich.repository.attribute.search');
    }

    private function addAttributeToFamilies(string $attributeCode, array $familyCodes): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::TEXT);
        $attribute->setCode($attributeCode);
        $attribute->setUnique(true);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        foreach ($familyCodes as $familyCode) {
            $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($familyCode);
            $family->addAttribute($attribute);
            $this->get('pim_catalog.saver.family')->save($family);
        }
    }

    private function createFamily($familyCode)
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $family->setCode($familyCode);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    private function validateSameAttributeCodes(array $result, array $expected)
    {
        $resultCodes = array_map(function (AttributeInterface $attr) {
            return $attr->getCode();
        }, $result);

        Assert::same($resultCodes, $expected);
    }
}
