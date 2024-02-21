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
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
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

    protected function createProduct(string $identifier, array $userIntents = []): ProductInterface
    {
        $this->upsertProduct($identifier, $userIntents);

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    protected function upsertProduct(string $identifier, array $userIntents): void
    {
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product afterward
        $command = UpsertProductCommand::createWithIdentifierSystemUser($identifier, $userIntents);

        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }

    protected function getProductUuidFromIdentifier(string $identifier): ?UuidInterface
    {
        $envelope = $this->get('pim_enrich.product.query_message_bus')->dispatch(new GetProductUuidsQuery([
            'identifier' => [
                [
                    'operator' => Operators::EQUALS,
                    'value' => $identifier,
                ],
            ],
        ], -1));

        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::notNull($handledStamp, 'The query bus does not return any result when searching product uuid');

        $uuids = \iterator_to_array($handledStamp->getResult());

        return $uuids[0] ?? null;
    }

    protected function createProductWithoutEvaluations(string $identifier, array $userIntents = []): ProductInterface
    {
        $product = $this->createProduct($identifier, $userIntents);
        $this->deleteProductCriterionEvaluations($product->getUuid());

        return $product;
    }

    protected function createMinimalProductVariant(string $identifier, string $parent, string $axisOption, array $userIntents = []): ProductInterface
    {
        Assert::oneOf($axisOption, self::MINIMAL_VARIANT_OPTIONS, 'Unknown minimal variant option');

        $userIntents[] = new ChangeParent($parent);
        $userIntents[] = new SetSimpleSelectValue(self::MINIMAL_VARIANT_AXIS_CODE, null, null, $axisOption);

        return $this->createProduct($identifier, $userIntents);
    }

    protected function createProductModel(string $code, string $familyVariant, array $data = []): ProductModelInterface
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->build();

        return $this->updateProductModel($productModel, $data);
    }

    /**
     * @return ProductModelInterface[]
     */
    protected function createProductModels(array $codes, string $familyVariant, array $data = []): array
    {
        $productModels = [];
        foreach ($codes as $code) {
            $productModels[] = $this->get('akeneo_integration_tests.catalog.product_model.builder')
                ->withCode($code)
                ->withFamilyVariant($familyVariant)
                ->build();
        }

        return $this->updateProductModels($productModels, $data);
    }

    protected function updateProductModel(ProductModelInterface $productModel, array $data = []): ProductModelInterface
    {
        if (!empty($data)) {
            $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
            $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
            Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid product model', $errors));
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    /**
     * @param ProductModelInterface[] $productModels
     * @return ProductModelInterface[]
     */
    protected function updateProductModels(array $productModels, array $data = []): array
    {
        foreach ($productModels as $productModel) {
            if (!empty($data)) {
                $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
                $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
                Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid product model', $errors));
            }
        }

        $this->get('pim_catalog.saver.product_model')->saveAll($productModels);

        return $productModels;
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

    protected function simulateAttributeSpellcheckEvaluationOnProduct(UuidInterface $uuid): void
    {
        $query = <<<SQL
INSERT INTO pim_data_quality_insights_product_criteria_evaluation (product_uuid, criterion_code, evaluated_at, status, result)
VALUES (:uuid, 'consistency_attribute_spelling', now(), 'done', '{}') AS product_score_values
ON DUPLICATE KEY UPDATE
    evaluated_at = product_score_values.evaluated_at,
    status = product_score_values.status,
    result = product_score_values.result;
SQL;
        $this->get('database_connection')->executeQuery($query, ['uuid' => $uuid->getBytes()]);
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

    protected function simulateAttributeEvaluationOnProductModel(int $productModelId): void
    {
        $query = <<<SQL
INSERT INTO pim_data_quality_insights_product_model_criteria_evaluation (product_id, criterion_code, evaluated_at, status, result)
VALUES (:id, 'consistency_attribute_spelling', now(), 'done', '{}') AS score_values
ON DUPLICATE KEY UPDATE
    evaluated_at = score_values.evaluated_at,
    status = score_values.status,
    result = score_values.result;
SQL;
        $this->get('database_connection')->executeQuery($query, ['id' => $productModelId]);
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

    protected function assertProductScoreIsComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertTrue(
            $this->isProductScoreComputed($productUuid, $evaluatedAt),
            \sprintf('Product evaluation does not exist. Product uuid: %s', $productUuid->__toString())
        );
    }

    protected function assertProductScoreIsNotComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertFalse(
            $this->isProductScoreComputed($productUuid, $evaluatedAt),
            \sprintf('Product evaluation exists, it should not. Product uuid: %s', $productUuid->__toString())
        );
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

    protected function assertProductModelScoreIsComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertTrue(
            $this->isProductModelScoreComputed($productModelId, $evaluatedAt),
            \sprintf('Product model evaluation does not exist. Product model id: %s', $productModelId->__toString())
        );
    }

    protected function assertProductModelScoreIsNotComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertFalse(
            $this->isProductModelScoreComputed($productModelId, $evaluatedAt),
            \sprintf('Product model evaluation exists, it should not. Product model id: %s', $productModelId->__toString())
        );
    }

    protected function simulateOldProductScoreCompute(): void
    {
        $this->get('database_connection')->executeQuery(
            'UPDATE pim_data_quality_insights_product_score SET evaluated_at = "1980-01-01"'
        );
    }

    protected function simulateOldProductModelScoreCompute(): void
    {
        $this->get('database_connection')->executeQuery(
            'UPDATE pim_data_quality_insights_product_model_score SET evaluated_at = "1980-01-01"'
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
