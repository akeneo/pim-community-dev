<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

abstract class EnrichmentProductModelTestCase extends TestCase
{
    protected PubSubQueueStatus $pubSubQueueStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pubSubQueueStatus = $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_product_model_score_compute_on_upsert');
        $this->pubSubQueueStatus->flushJobQueue();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
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

    protected function ensureAttributeGroupExists(string $code): void
    {
        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier($code);
        if (null === $attributeGroup) {
            $this->createAttributeGroup($code);
        }
    }

    protected function createAttributeGroup(string $code, array $data = []): AttributeGroupInterface
    {
        $data = array_merge(['code' => $code], $data);

        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, $data);
        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        return $attributeGroup;
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

    protected function createProductModel(string $code, string $familyVariant, array $data = [], bool $save = true): ProductModelInterface
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->build();

        return $this->updateProductModel($productModel, $data, $save);
    }

    protected function updateProductModel(ProductModelInterface $productModel, array $data = [], bool $save = true): ProductModelInterface
    {
        if (!empty($data)) {
            $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
            $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
            Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid product model', $errors));
        }

        if ($save === true) {
            $this->get('pim_catalog.saver.product_model')->save($productModel);
        }

        return $productModel;
    }

    /**
     * @return ProductModelInterface[]
     */
    protected function createProductModels(array $codes, string $familyVariant, array $data = [], bool $save = true): array
    {
        $productModels = [];
        foreach ($codes as $code) {
            $productModels[] = $this->get('akeneo_integration_tests.catalog.product_model.builder')
                ->withCode($code)
                ->withFamilyVariant($familyVariant)
                ->build();
        }

        return $this->updateProductModels($productModels, $data, $save);
    }

    /**
     * @param ProductModelInterface[] $productModels
     * @return ProductModelInterface[]
     */
    protected function updateProductModels(array $productModels, array $data = [], bool $save = true): array
    {
        foreach ($productModels as $productModel) {
            if (!empty($data)) {
                $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
                $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);
                Assert::count($errors, 0, $this->formatValidationErrorMessage('Invalid product model', $errors));
            }
        }

        if ($save === true) {
            $this->get('pim_catalog.saver.product_model')->saveAll($productModels);
        }

        return $productModels;
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
