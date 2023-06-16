<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Code;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateAttributeSqlIntegration extends CategoryTestCase
{
    public function testAttributeHasBeenDeactivated(): void
    {
        $category = $this->insertBaseCategory(new Code('template_model'));
        $mockedTemplate = $this->generateMockedCategoryTemplateModel(
            categoryTreeId: $category->getId()->getValue()
        );

        $activateTemplate = $this->get(ActivateTemplate::class);
        $templateUuid = ($activateTemplate)(
            $mockedTemplate->getCategoryTreeId(),
            $mockedTemplate->getCode(),
            $mockedTemplate->getLabelCollection()
        );

        /** @var AttributeCollection $insertedAttributes */
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateUuid);

        $this->assertCount(13, $insertedAttributes);
        foreach(range(0,2) as $index) {
            $attributeUuid = $insertedAttributes->getAttributes()[$index]->getUuid();
            $this::assertFalse($this->retrieveAttributeDeactivationStatus($attributeUuid));
            $this->get(DeactivateAttribute::class)->execute($templateUuid, $attributeUuid);
            $this::assertTrue($this->retrieveAttributeDeactivationStatus($attributeUuid));
        }
    }

    public function testItDoesNotCrashIfTemplateDoesNotExists(): void
    {
        $nonExistingAttributeUuid = AttributeUuid::fromString('583393ac-ca2a-11ed-afa1-0242ac120002');
        try {
            $this::assertFalse($this->retrieveAttributeDeactivationStatus($nonExistingAttributeUuid));
        } catch (\Exception $e) {
            $this->fail('An unexpected exception was thrown: '.$e->getMessage());
        }
    }

    private function retrieveAttributeDeactivationStatus(AttributeUuid $attributeUuid): bool
    {
        $query = <<<SQL
            SELECT is_deactivated 
            FROM pim_catalog_category_attribute
            WHERE uuid = :attribute_uuid;
        SQL;

        return (bool) $this->get('database_connection')->executeQuery(
            $query,
            [
                'attribute_uuid' => $attributeUuid->toBytes(),
            ],
            [
                'attribute_uuid' => \PDO::PARAM_STR,
            ],
        )->fetchOne();

    }
}
