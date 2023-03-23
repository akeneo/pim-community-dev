<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\DeactivateTemplate;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
use Akeneo\Category\Domain\Query\DeleteTemplateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

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
            $this::assertFalse($this->retrieveAttributeDeactivationStatus($templateUuid, $attributeUuid));
            $this->get(DeactivateAttribute::class)->execute($templateUuid, $attributeUuid);
            $this::assertTrue($this->retrieveAttributeDeactivationStatus($templateUuid, $attributeUuid));
        }
    }

    public function testItDoesNotCrashIfTemplateDoesNotExists(): void
    {
        $nonExistingTemplateUuid = TemplateUuid::fromString('a1b744e2-a84b-4f74-832f-01aeb202d0ce');
        $nonExistingAttributeUuid = AttributeUuid::fromString('583393ac-ca2a-11ed-afa1-0242ac120002');
        try {
            $this::assertFalse($this->retrieveAttributeDeactivationStatus($nonExistingTemplateUuid, $nonExistingAttributeUuid));
        } catch (\Exception $e) {
            $this->fail('An unexpected exception was thrown: '.$e->getMessage());
        }
    }

    private function retrieveAttributeDeactivationStatus(TemplateUuid $templateUuid, AttributeUuid $attributeUuid): bool
    {
        $query = <<<SQL
            SELECT is_deactivated 
            FROM pim_catalog_category_attribute
            WHERE category_template_uuid = :template_uuid
            AND uuid = :attribute_uuid;
        SQL;

        return (bool) $this->get('database_connection')->executeQuery(
            $query,
            [
                'template_uuid' => $templateUuid->toBytes(),
                'attribute_uuid' => $attributeUuid->toBytes(),
            ],
            [
                'template_uuid' => \PDO::PARAM_STR,
                'attribute_uuid' => \PDO::PARAM_STR,
            ],
        )->fetchOne();

    }
}
