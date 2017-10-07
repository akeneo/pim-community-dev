<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCompletenessTestCase extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param ProductInterface $product
     *
     * @return CompletenessInterface
     */
    protected function getCurrentCompleteness(ProductInterface $product)
    {
        $completenesses = $product->getCompletenesses()->toArray();

        return current($completenesses);
    }

    /**
     * @param CompletenessInterface $completeness
     * @param string[]              $expectedAttributeCodes
     */
    protected function assertMissingAttributeCodes(CompletenessInterface $completeness, array $expectedAttributeCodes)
    {
        $missingAttributes = $completeness->getMissingAttributes();

        $missingAttributeCodes = array_map(function (AttributeInterface $missingAttribute) {
            return $missingAttribute->getCode();
        }, $missingAttributes->toArray());

        $this->assertEquals(sort($expectedAttributeCodes), sort($missingAttributeCodes));
    }

    /**
     * @param ProductInterface $product
     * @param int              $expectedNumberOfCompletenesses
     */
    protected function assertCompletenessesCount(ProductInterface $product, $expectedNumberOfCompletenesses)
    {
        $completenesses = $product->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount($expectedNumberOfCompletenesses, $completenesses);
    }

    /**
     * @param string $code
     * @param string $type
     * @param bool   $localisable
     * @param bool   $scopable
     * @param array  $localesSpecific
     *
     * @return AttributeInterface
     */
    protected function createAttribute(
        $code,
        $type,
        $localisable = false,
        $scopable = false,
        array $localesSpecific = []
    ) {
        $group = $this->get('pim_api.repository.attribute_group')->findOneByIdentifier('other');

        $attributeFactory = $this->get('pim_catalog.factory.attribute');
        $attributeSaver = $this->get('pim_catalog.saver.attribute');

        $attribute = $attributeFactory->createAttribute($type);
        $attribute->setCode($code);
        $attribute->setLocalizable($localisable);
        $attribute->setScopable($scopable);
        $attribute->setGroup($group);
        foreach ($localesSpecific as $locale) {
            $attribute->addAvailableLocale($locale);
        }

        $attributeSaver->save($attribute);

        return $attribute;
    }

    /**
     * @param FamilyInterface $family
     * @param string          $code
     * @param array           $standardValues
     *
     * @return ProductInterface
     */
    protected function createProductWithStandardValues(FamilyInterface $family, $code, $standardValues = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($code, $family->getCode());
        $this->get('pim_catalog.updater.product')->update($product, $standardValues);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param string $familyCode
     * @param string $channelCode
     * @param string $attributeCode
     * @param string $attributeType
     * @param bool $localisable
     * @param bool $scopable
     * @param LocaleInterface[] $localesSpecific
     *
     * @return FamilyInterface
     */
    protected function createFamilyWithRequirement(
        $familyCode,
        $channelCode,
        $attributeCode,
        $attributeType,
        $localisable = false,
        $scopable = false,
        array $localesSpecific = []
    ) {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        $attribute = $this->createAttribute($attributeCode, $attributeType, $localisable, $scopable, $localesSpecific);

        $requirement = $this->get('pim_catalog.factory.attribute_requirement')
            ->createAttributeRequirement($attribute, $channel, true);

        $family = $this->findOrCreateFamily($familyCode);
        $family->addAttribute($attribute);
        $family->addAttributeRequirement($requirement);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * @param string $familyCode
     * @param string $channelCode
     * @param string $attributeCode
     *
     * @return FamilyInterface
     */
    protected function addFamilyRequirement($familyCode, $channelCode, $attributeCode)
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode);

        $requirement = $this->get('pim_catalog.factory.attribute_requirement')
            ->createAttributeRequirement($attribute, $channel, true);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($familyCode);
        $family->addAttribute($attribute);
        $family->addAttributeRequirement($requirement);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * @param string $code
     *
     * @return FamilyInterface
     */
    private function findOrCreateFamily($code)
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($code);
        if (null === $family) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $family->setCode($code);
        }

        return $family;
    }
}
