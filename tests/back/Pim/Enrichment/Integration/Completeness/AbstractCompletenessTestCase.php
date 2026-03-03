<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\TestCase;

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

    protected function getCurrentCompleteness(ProductInterface $product): ProductCompleteness
    {
        $completenesses = $this->getProductCompletenesses()->fromProductUuid($product->getUuid());

        return $completenesses->getIterator()->current();
    }

    protected function assertCompletenessesCount(ProductInterface $product, int $expectedNumberOfCompletenesses)
    {
        $completenesses = $this->getProductCompletenesses()->fromProductUuid($product->getUuid());
        $this->assertCount($expectedNumberOfCompletenesses, $completenesses);
    }

    protected function createAttribute(
        string $code,
        string $type,
        bool $localisable = false,
        bool $scopable = false,
        array $localesSpecific = [],
        AttributeGroup $group = null
    ): AttributeInterface {
        if (null === $group) {
            $group = $this->get('pim_api.repository.attribute_group')->findOneByIdentifier('other');
        }

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
     * @param UserIntent[] $userIntents
     */
    protected function createProductWithStandardValues(string $identifier, array $userIntents = []): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    /**
     * @param LocaleInterface[] $localesSpecific
     *
     * @return FamilyInterface
     */
    protected function createFamilyWithRequirement(
        string $familyCode,
        string $channelCode,
        string $attributeCode,
        string $attributeType,
        bool $localisable = false,
        bool $scopable = false,
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
    protected function findOrCreateFamily($code)
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
