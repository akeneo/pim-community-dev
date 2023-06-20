<?php

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommand;
use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommandHandler;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

class UpdateCategoryTemplateAttributesOrderSqlIntegration  extends CategoryTestCase
{
    private TemplateUuid $templateUuid;
    private CreateTemplateCommandHandler $createTemplateCommandHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTemplateCommandHandler = $this->get(CreateTemplateCommandHandler::class);
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
        $this->templateUuid = ($this->getTemplate)($category->getId())->getUuid();
    }
    public function testItUpdatesAttributesOrder(): void
    {
        /** @var Attribute $longDescriptionAttribute */
        $longDescriptionAttribute =  $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid)
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

        /**
         * @var UpdateCategoryTemplateAttributesOrder $updateCategoryTemplateAttributesOrder
         */
        $updateCategoryTemplateAttributesOrder = $this->get(UpdateCategoryTemplateAttributesOrder::class);
        $updateCategoryTemplateAttributesOrder->fromAttributeCollection(
            AttributeCollection::fromArray([$toUpdateAttribute])
        );

        /** @var Attribute $updatedLongDescriptionAttribute */
        $updatedLongDescriptionAttribute =  $this->get(GetAttribute::class)->byTemplateUuid($this->templateUuid)
            ->getAttributeByCode('long_description');

        $this->assertEquals(200, $updatedLongDescriptionAttribute->getOrder()->intValue());
    }
}
