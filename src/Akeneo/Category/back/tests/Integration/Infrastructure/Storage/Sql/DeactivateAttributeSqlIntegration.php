<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommand;
use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommandHandler;
use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateAttributeSqlIntegration extends CategoryTestCase
{
    private CreateTemplateCommandHandler $createTemplateCommandHandler;
    private GetCategoryTemplateByCategoryTree $getTemplate;
    private CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver;
    private DeactivateAttribute $deactivateAttribute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTemplateCommandHandler = $this->get(CreateTemplateCommandHandler::class);
        $this->getTemplate = $this->get(GetCategoryTemplateByCategoryTree::class);
        $this->categoryTemplateAttributeSaver = $this->get(CategoryTemplateAttributeSaver::class);
        $this->deactivateAttribute = $this->get(DeactivateAttribute::class);
    }

    public function testAttributeHasBeenDeactivated(): void
    {
        $category = $this->insertBaseCategory(new Code('template_model'));
        $mockedTemplate = $this->generateMockedCategoryTemplateModel(
            categoryTreeId: $category->getId()->getValue()
        );
        $command = new CreateTemplateCommand(
            $mockedTemplate->getCategoryTreeId(),
            [
                'code' => (string) $mockedTemplate->getCode(),
                'labels' => $mockedTemplate->getLabelCollection()->normalize(),
            ]
        );
        ($this->createTemplateCommandHandler)($command);
        $templateUuid = ($this->getTemplate)($category->getId())->getUuid();

        $attributeToDeactivate = Attribute::fromType(
            type: new AttributeType(AttributeType::TEXT),
            uuid: AttributeUuid::fromUuid(Uuid::uuid4()),
            code: new AttributeCode('attribute_to_deactivate'),
            order: AttributeOrder::fromInteger(1),
            isRequired: AttributeIsRequired::fromBoolean(false),
            isScopable: AttributeIsScopable::fromBoolean(false),
            isLocalizable: AttributeIsLocalizable::fromBoolean(false),
            labelCollection: LabelCollection::fromArray([]),
            templateUuid: $templateUuid,
            additionalProperties: AttributeAdditionalProperties::fromArray([]),
        );

        $attributeToNotDeactivate = Attribute::fromType(
            type: new AttributeType(AttributeType::TEXT),
            uuid: AttributeUuid::fromUuid(Uuid::uuid4()),
            code: new AttributeCode('attribute_to_not_deactivate'),
            order: AttributeOrder::fromInteger(2),
            isRequired: AttributeIsRequired::fromBoolean(false),
            isScopable: AttributeIsScopable::fromBoolean(false),
            isLocalizable: AttributeIsLocalizable::fromBoolean(false),
            labelCollection: LabelCollection::fromArray([]),
            templateUuid: $templateUuid,
            additionalProperties: AttributeAdditionalProperties::fromArray([]),
        );

        $this->categoryTemplateAttributeSaver->insert($templateUuid, AttributeCollection::fromArray([
            $attributeToDeactivate,
            $attributeToNotDeactivate
        ]));

        $this::assertFalse($this->retrieveAttributeDeactivationStatus($attributeToDeactivate->getUuid()));
        $this::assertFalse($this->retrieveAttributeDeactivationStatus($attributeToNotDeactivate->getUuid()));
        $this->deactivateAttribute->execute($templateUuid, $attributeToDeactivate->getUuid());
        $this::assertTrue($this->retrieveAttributeDeactivationStatus($attributeToDeactivate->getUuid()));
        $this::assertFalse($this->retrieveAttributeDeactivationStatus($attributeToNotDeactivate->getUuid()));
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
