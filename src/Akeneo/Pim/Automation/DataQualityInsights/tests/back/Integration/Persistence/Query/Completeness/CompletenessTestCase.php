<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class CompletenessTestCase extends DataQualityInsightsTestCase
{
    protected function givenFamilies(array $familiesData): void
    {
        $families = array_map(function ($familyData) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update(
                $family,
                [
                    'code' => $familyData['code'],
                    'attributes'  =>  $familyData['attribute_codes'] ?? [],
                    'attribute_requirements' => $familyData['attribute_requirements'] ?? [],
                ]
            );

            $errors = $this->get('validator')->validate($family);
            Assert::count($errors, 0, sprintf('Family "%s" is invalid', $familyData['code']));

            return $family;
        }, $familiesData);

        $this->get('pim_catalog.saver.family')->saveAll($families);
    }

    protected function givenAFamilyVariant(array $familyVariantData): void
    {
        $family_variant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family_variant, $familyVariantData);

        $errors = $this->get('validator')->validate($family_variant);
        Assert::count($errors, 0);

        $this->get('pim_catalog.saver.family_variant')->save($family_variant);
    }

    protected function givenAttributes(array $attributesData): void
    {
        $attributes = array_map(function ($attributeData) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                [
                    'code' => $attributeData['code'],
                    'type' => $attributeData['type'],
                    'localizable' => $attributeData['localizable'] ?? false,
                    'scopable' => $attributeData['scopable'] ?? false,
                    'group' => $attributeData['group'] ?? 'other',
                    'available_locales' => $attributeData['available_locales'] ?? [],
                    'decimals_allowed' => $attributeData['type'] === AttributeTypes::PRICE_COLLECTION ? false : null,
                ]
            );

            $errors = $this->get('validator')->validate($attribute);
            Assert::count($errors, 0);

            return $attribute;
        }, $attributesData);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    protected function givenCurrencyForChannel(array $channelsData): void
    {
        $channels = array_map(function ($channelData) {
            $channel = $this->get('pim_catalog.repository.channel')->findOneBy(['code' => $channelData['code']]);
            $this->get('pim_catalog.updater.channel')->update(
                $channel,
                [
                    'currencies' => $channelData['currencies']
                ]
            );

            $errors = $this->get('validator')->validate($channel);
            Assert::count($errors, 0);

            return $channel;
        }, $channelsData);

        $this->saveChannels($channels);
    }

    protected function givenChannels(array $channelsData): void
    {
        $channels = array_map(function ($channelData) {
            $channel = $this->get('pim_catalog.factory.channel')->create();
            $this->get('pim_catalog.updater.channel')->update(
                $channel,
                [
                    'code' => $channelData['code'],
                    'locales' => $channelData['locales'],
                    'currencies' => $channelData['currencies'],
                    'category_tree' => 'master'
                ]
            );

            $errors = $this->get('validator')->validate($channel);
            Assert::count($errors, 0);

            return $channel;
        }, $channelsData);

        $this->saveChannels($channels);
    }

    protected function givenAProductModel(string $productModelCode, string $familyVariant): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId((int) $productModel->getId());
    }

    protected function givenASubProductModel(string $productModelCode, string $familyVariant, string $parentCode): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant($familyVariant)
            ->withParent($parentCode)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId((int) $productModel->getId());
    }

    protected function givenADeactivatedAttributeGroup(string $code): void
    {
        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, ['code' => $code]);
        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        $attributeGroupActivation = new AttributeGroupActivation(new AttributeGroupCode($code), false);
        $this->get(AttributeGroupActivationRepository::class)->save($attributeGroupActivation);
    }
}
