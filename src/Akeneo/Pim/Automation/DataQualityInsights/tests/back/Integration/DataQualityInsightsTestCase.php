<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
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
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::count($errors, 0, 'Invalid product');

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
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
}
