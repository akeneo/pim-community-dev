<?php

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommand;
use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommandHandler;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;
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
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Ramsey\Uuid\Uuid;

class UpdateCategoryTemplateAttributesOrderSqlIntegration  extends CategoryTestCase
{
    private UpdateCategoryTemplateAttributesOrder $updateCategoryTemplateAttributesOrder;

    private TemplateUuid $templateUuid;
    private CreateTemplateCommandHandler $createTemplateCommandHandler;
    private GetCategoryTemplateByCategoryTree $getCategoryTemplateByCategoryTree;
    private CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver;
    private GetAttribute $getAttribute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTemplateCommandHandler = $this->get(CreateTemplateCommandHandler::class);
        $this->getCategoryTemplateByCategoryTree = $this->get(GetCategoryTemplateByCategoryTree::class);
        $this->categoryTemplateAttributeSaver = $this->get(CategoryTemplateAttributeSaver::class);
        $this->updateCategoryTemplateAttributesOrder = $this->get(UpdateCategoryTemplateAttributesOrder::class);
        $this->getAttribute = $this->get(GetAttribute::class);

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
        $this->templateUuid = ($this->getCategoryTemplateByCategoryTree)($category->getId())->getUuid();

        $attribute = Attribute::fromType(
            type: new AttributeType(AttributeType::TEXTAREA),
            uuid: AttributeUuid::fromUuid(Uuid::uuid4()),
            code: new AttributeCode('long_description'),
            order: AttributeOrder::fromInteger(2),
            isRequired: AttributeIsRequired::fromBoolean(false),
            isScopable: AttributeIsScopable::fromBoolean(false),
            isLocalizable: AttributeIsLocalizable::fromBoolean(false),
            labelCollection: LabelCollection::fromArray([]),
            templateUuid: $this->templateUuid,
            additionalProperties: AttributeAdditionalProperties::fromArray([]),
        );

        $this->categoryTemplateAttributeSaver->insert($this->templateUuid, AttributeCollection::fromArray([$attribute]));
    }

    public function testItUpdatesAttributesOrder(): void
    {
        $longDescriptionAttribute =  $this->getAttribute->byTemplateUuid($this->templateUuid)
            ->getAttributeByCode('long_description');

        $toUpdateAttribute = Attribute::fromType(
            $longDescriptionAttribute->getType(),
            $longDescriptionAttribute->getUuid(),
            $longDescriptionAttribute->getCode(),
            AttributeOrder::fromInteger(200),
            $longDescriptionAttribute->isRequired(),
            $longDescriptionAttribute->isScopable(),
            $longDescriptionAttribute->isLocalizable(),
            $longDescriptionAttribute->getLabelCollection(),
            $longDescriptionAttribute->getTemplateUuid(),
            $longDescriptionAttribute->getAdditionalProperties(),
        );

        $this->updateCategoryTemplateAttributesOrder->fromAttributeCollection(
            AttributeCollection::fromArray([$toUpdateAttribute])
        );

        $updatedLongDescriptionAttribute =  $this->getAttribute->byTemplateUuid($this->templateUuid)
            ->getAttributeByCode('long_description');

        $this->assertEquals(200, $updatedLongDescriptionAttribute->getOrder()->intValue());
    }

    public function testItDoesNotUpdateAttributesOrderForAnEmptyAttributeCollection(): void
    {
        // When

        $this->updateCategoryTemplateAttributesOrder->fromAttributeCollection(
            AttributeCollection::fromArray([])
        );

        // Then

        $this->doesNotPerformAssertions();
    }
}
