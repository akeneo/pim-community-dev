<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

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
     * @return ProductCompleteness
     */
    protected function getCurrentCompleteness(ProductInterface $product)
    {
        $completenesses = $this->getProductCompletenesses()->fromProductId($product->getId());

        return $completenesses->getIterator()->current();
    }

    /**
     * @param ProductInterface $product
     * @param int              $expectedNumberOfCompletenesses
     */
    protected function assertCompletenessesCount(ProductInterface $product, $expectedNumberOfCompletenesses)
    {
        $completenesses = $this->getProductCompletenesses()->fromProductId($product->getId());
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
        Assert::assertCount(0, $this->get('validator')->validate($product));
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        return $product;
    }

    /**
     * @param string            $familyCode
     * @param string            $channelCode
     * @param string            $attributeCode
     * @param string            $attributeType
     * @param bool              $localisable
     * @param bool              $scopable
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
        if (!$family->hasAttributeCode($attributeCode)) {
            $family->addAttribute($attribute);
        }
        $family->addAttributeRequirement($requirement);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function removeFamilyRequirement($familyCode, $channelCode, $attributeCode): void
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier($familyCode);
        $attributeRequirementToRemove = $this->getAttributeRequirement($family, $channelCode, $attributeCode);
        $family->removeAttributeRequirement($attributeRequirementToRemove);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * @return GetProductCompletenesses
     */
    protected function getProductCompletenesses(): GetProductCompletenesses
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses');
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

    /**
     * @param FamilyInterface $family
     * @param string          $channelCode
     * @param string          $attributeCode
     *
     * @return null|AttributeRequirementInterface
     */
    private function getAttributeRequirement(
        FamilyInterface $family,
        string $channelCode,
        string $attributeCode
    ): ?AttributeRequirementInterface {
        $attributeRequirementToRemove = null;

        $attributeRequirements = $family->getAttributeRequirements();
        foreach ($attributeRequirements as $attributeRequirement) {
            if ($channelCode === $attributeRequirement->getChannelCode() &&
                $attributeCode === $attributeRequirement->getAttributeCode()
            ) {
                $attributeRequirementToRemove = $attributeRequirement;
                break;
            }
        }

        return $attributeRequirementToRemove;
    }
}
