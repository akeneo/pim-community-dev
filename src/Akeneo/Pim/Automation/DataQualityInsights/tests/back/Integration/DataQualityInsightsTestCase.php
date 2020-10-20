<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataQualityInsightsTestCase extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);

        if (!empty($data)) {
            $this->get('pim_catalog.updater.product')->update($product, $data);
            $errors = $this->get('pim_catalog.validator.product')->validate($product);
            Assert::count($errors, 0, 'Invalid product');
        }

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    protected function createProductModel(string $code, string $familyVariant, array $data = []): ProductModelInterface
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->build();

        if (!empty($data)) {
            $this->get('pim_catalog.updater.product')->update($productModel, $data);
            $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
            Assert::count($errors, 0, 'Invalid product model');
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    protected function createSubProductModel(string $code, string $familyVariant, string $parent, array $data = []): ProductModelInterface
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->withParent($parent)
            ->build();

        if (!empty($data)) {
            $this->get('pim_catalog.updater.product')->update($productModel, $data);
            $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
            Assert::count($errors, 0, 'Invalid product model');
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    protected function createFamily(string $code, array $data = []): FamilyInterface
    {
        $data = array_merge(['code' => $code], $data);

        $family = $this->get('akeneo_integration_tests.base.family.builder')->build($data);

        $errors = $this->get('validator')->validate($family);
        Assert::count($errors, 0, 'Invalid family');

        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function createFamilyVariant(string $code, string $family, array $data = []): FamilyVariantInterface
    {
        $data = array_merge(['code' => $code, 'family' => $family], $data);

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);

        $errors = $this->get('validator')->validate($familyVariant);
        Assert::count($errors, 0);

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    protected function createAttribute(string $code, array $data = []): AttributeInterface
    {
        $defaultData = [
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ];
        $data = array_merge($defaultData, $data);

        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build($data, true);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    protected function createAttributeGroup(string $code, array $data = []): AttributeGroupInterface
    {
        $data = array_merge(['code' => $code], $data);

        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, $data);
        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        return $attributeGroup;
    }

    protected function createAttributeOptions(string $attributeCode, array $optionsCodes): void
    {
        $attributeOptions = [];
        foreach ($optionsCodes as $optionCode) {
            $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
            $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
                'code' => $optionCode,
                'attribute' => $attributeCode,
            ]);
            $attributeOptions[] = $attributeOption;
        }

        $this->get('pim_catalog.saver.attribute_option')->saveAll($attributeOptions);
    }

    protected function createAttributeGroupActivation(string $code, bool $activated = true, ?\DateTimeImmutable $updatedAt = null): AttributeGroupActivation
    {
        $attributeGroupActivation = new AttributeGroupActivation(new AttributeGroupCode($code), $activated);
        $this->get(AttributeGroupActivationRepository::class)->save($attributeGroupActivation);

        if (null !== $updatedAt) {
            $this->get('database_connection')->executeQuery(
                <<<SQL
UPDATE pim_data_quality_insights_attribute_group_activation
SET updated_at = :updatedAt WHERE attribute_group_code = :attributeGroupCode
SQL
            , [
                'updatedAt' => $updatedAt->format(Clock::TIME_FORMAT),
                'attributeGroupCode' => $code,
            ]);
        }

        return $attributeGroupActivation;
    }

    protected function updateProductEvaluationsAt(int $productId, string $status, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation 
SET status = :status, evaluated_at = :evaluatedAt
WHERE product_id = :productId;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'status' => $status,
            'evaluatedAt' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'productId' => $productId,
        ]);
    }

    protected function updateProductModelEvaluationsAt(int $productModelId, string $status, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation 
SET status = :status, evaluated_at = :evaluatedAt
WHERE product_id = :productModelId;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'status' => $status,
            'evaluatedAt' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'productModelId' => $productModelId,
        ]);
    }

    protected function createChannel(string $code, array $data = []): ChannelInterface
    {
        $defaultData = [
            'code' => $code,
            'locales' => ['en_US'],
            'currencies' => ['USD'],
            'category_tree' => 'master',
        ];
        $data = array_merge($defaultData, $data);

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($code);
        if (null === $channel) {
            $channel = $this->get('pim_catalog.factory.channel')->create();
        }

        $this->get('pim_catalog.updater.channel')->update($channel, $data);
        $errors = $this->get('validator')->validate($channel);
        Assert::count($errors, 0, 'Invalid channel');

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }

    protected function getLocaleId(string $code): int
    {
        $localeId = $this->get('database_connection')->executeQuery(
            'SELECT id FROM pim_catalog_locale WHERE code = :code',
            ['code' => $code]
        )->fetchColumn();

        return intval($localeId);
    }
}
