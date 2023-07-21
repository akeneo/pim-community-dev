<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataQualityInsightsTestCase extends TestCase
{
    protected const MINIMAL_VARIANT_AXIS_CODE = 'color';
    protected const MINIMAL_VARIANT_OPTIONS = ['red', 'blue', 'yellow', 'black', 'white'];

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function getRandomCode(): string
    {
        return Uuid::uuid4()->toString();
    }

    protected function deleteProductCriterionEvaluations(UuidInterface $productUuid): void
    {
        $this->get('database_connection')->executeQuery(
            'DELETE FROM pim_data_quality_insights_product_criteria_evaluation WHERE product_uuid = :productUuid',
            ['productUuid' => $productUuid->getBytes()]
        );
    }

    protected function deleteAllProductCriterionEvaluations(): void
    {
        $this->get('database_connection')->executeQuery('DELETE FROM pim_data_quality_insights_product_criteria_evaluation');
    }

    protected function deleteAllProductModelCriterionEvaluations(): void
    {
        $this->get('database_connection')->executeQuery('DELETE FROM pim_data_quality_insights_product_model_criteria_evaluation');
    }

    protected function deleteProductModelCriterionEvaluations(int $productModelId): void
    {
        $this->get('database_connection')->executeQuery(
            'DELETE FROM pim_data_quality_insights_product_model_criteria_evaluation WHERE product_id = :productModelId',
            ['productModelId' => $productModelId]
        );
    }

    protected function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);

        if (!empty($data)) {
            $this->get('pim_catalog.updater.product')->update($product, $data);
            $errors = $this->get('pim_catalog.validator.product')->validate($product);
            Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid product', $errors));
        }

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    protected function createProductWithoutEvaluations(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->createProduct($identifier, $data);
        $this->deleteProductCriterionEvaluations($product->getUuid());

        return $product;
    }

    protected function createMinimalProductVariant(string $identifier, string $parent, string $axisOption, array $data = []): ProductInterface
    {
        Assert::oneOf($axisOption, self::MINIMAL_VARIANT_OPTIONS, 'Unknown minimal variant option');

        $data['parent'] = $parent;
        $data['values'][self::MINIMAL_VARIANT_AXIS_CODE] = [
            ['data' => $axisOption, 'scope' => null, 'locale' => null],
        ];

        return $this->createProduct($identifier, $data);
    }

    protected function createProductModel(string $code, string $familyVariant, array $data = []): ProductModelInterface
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->build();

        if (!empty($data)) {
            $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
            $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
            Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid product model', $errors));
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    protected function createProductModelWithoutEvaluations(string $code, string $familyVariant, array $data = []): ProductModelInterface
    {
        $productModel = $this->createProductModel($code, $familyVariant, $data);
        $this->deleteProductModelCriterionEvaluations($productModel->getId());

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
            $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
            $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
            Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid sub-product model', $errors));
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    protected function createFamily(string $code, array $data = []): FamilyInterface
    {
        $data = array_merge(['code' => $code], $data);
        $data['attributes'] = array_merge(['sku'], $data['attributes'] ?? []);

        $family = $this->get('akeneo_integration_tests.base.family.builder')->build($data);

        $errors = $this->get('validator')->validate($family);
        Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid family', $errors));

        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function createFamilyVariant(string $code, string $family, array $data = []): FamilyVariantInterface
    {
        $data = array_merge(['code' => $code, 'family' => $family], $data);

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);

        $errors = $this->get('validator')->validate($familyVariant);
        Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid family variant', $errors));

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    protected function createMinimalFamilyAndFamilyVariant(string $familyCode, string $familyVariantCode): void
    {
        $axis = $this->createSimpleSelectAttributeWithOptions(self::MINIMAL_VARIANT_AXIS_CODE, self::MINIMAL_VARIANT_OPTIONS);

        $this->createFamily($familyCode, ['attributes' => [$axis->getCode()]]);
        $this->createFamilyVariant($familyVariantCode, $familyCode, [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => [$axis->getCode()],
                    'attributes' => [],
                ],
            ]
        ]);
    }

    protected function createAttribute(string $code, array $data = []): AttributeInterface
    {
        $defaultData = [
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ];
        $data = array_merge($defaultData, $data);

        $this->ensureAttributeGroupExists($defaultData['group']);

        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build($data, true);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    protected function createSimpleSelectAttributeWithOptions(string $code, array $optionCodes): AttributeInterface
    {
        $attribute = $this->createAttribute($code, ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);

        foreach ($optionCodes as $sortOrder => $optionCode) {
            $option = $this->get('pim_catalog.factory.attribute_option')->create();
            $option->setCode($optionCode);
            $option->setAttribute($attribute);
            $option->setSortOrder($sortOrder);
            $this->get('pim_catalog.saver.attribute_option')->save($option);
        }

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

    protected function ensureAttributeGroupExists(string $code): void
    {
        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier($code);
        if (null === $attributeGroup) {
            $this->createAttributeGroup($code);
        }
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
                ,
                [
                'updatedAt' => $updatedAt->format(Clock::TIME_FORMAT),
                'attributeGroupCode' => $code,
            ]
            );
        }

        return $attributeGroupActivation;
    }

    protected function updateProductEvaluationsAt(UuidInterface $uuid, string $status, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation e, pim_catalog_product p
SET e.status = :status, e.evaluated_at = :evaluatedAt
WHERE p.uuid = :productUuid AND e.product_uuid = p.uuid;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'status' => $status,
            'evaluatedAt' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'productUuid' => $uuid->getBytes(),
        ], [
            'productUuid' => \PDO::PARAM_STR,
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
        Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid channel', $errors));

        $this->saveChannels([$channel]);

        return $channel;
    }

    protected function saveChannels(array $channels): void
    {
        $this->get('pim_catalog.saver.channel')->saveAll($channels);

        // Kill background process to avoid a race condition during loading fixtures for the next integration test.
        // @see DAPI-1477
        exec('pkill -f "remove_completeness_for_channel_and_locale"');
    }

    protected function getLocaleId(string $code): int
    {
        $localeId = $this->get('database_connection')->executeQuery(
            'SELECT id FROM pim_catalog_locale WHERE code = :code',
            ['code' => $code]
        )->fetchOne();

        return intval($localeId);
    }

    protected function resetProductsScores(): void
    {
        $this->get('database_connection')->executeQuery(
            <<<SQL
TRUNCATE TABLE pim_data_quality_insights_product_score;
SQL
        );
    }

    protected function resetProductModelsScores(): void
    {
        $this->get('database_connection')->executeQuery(
            <<<SQL
TRUNCATE TABLE pim_data_quality_insights_product_model_score;
SQL
        );
    }

    protected function isProductScoreComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): bool {
        return (bool) $this->get('database_connection')->executeQuery(
            <<<SQL
                SELECT product_uuid
                FROM pim_data_quality_insights_product_score
                WHERE product_uuid = :product_uuid AND evaluated_at = :evaluated_at
            SQL,
            ['product_uuid' => $productUuid->toBytes(), 'evaluated_at' => $evaluatedAt->format('Y-m-d')]
        )->fetchOne();
    }

    protected function isProductModelScoreComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): bool {
        return (bool) $this->get('database_connection')->executeQuery(
            <<<SQL
                SELECT product_model_id
                FROM pim_data_quality_insights_product_model_score
                WHERE product_model_id = :product_model_id AND evaluated_at = :evaluated_at
            SQL,
            ['product_model_id' => $productModelId->toInt(), 'evaluated_at' => $evaluatedAt->format('Y-m-d')]
        )->fetchOne();
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    protected function simulateOldProductScoreCompute(): void
    {
        $this->get('database_connection')->executeQuery(
            'UPDATE pim_data_quality_insights_product_score SET evaluated_at = "1980-01-01"'
        );
    }

    private function formatValidationErrorMessage(string $mainMessage, ConstraintViolationListInterface $errors): string
    {
        $errorMessage = '';
        foreach ($errors as $error) {
            $errorMessage .= PHP_EOL.$error->getMessage();
        }

        return $mainMessage.$errorMessage;
    }
}
