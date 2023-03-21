<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Command\CleanCategoryTemplateAndEnrichedValues;

use Akeneo\Category\Application\Command\CleanCategoryTemplateAndEnrichedValues\CleanCategoryTemplateAndEnrichedValuesCommand;
use Akeneo\Category\Application\Command\CleanCategoryTemplateAndEnrichedValues\CleanCategoryTemplateAndEnrichedValuesCommandHandler;
use Akeneo\Category\Application\Enrichment\CategoryAttributeValuesCleaner;
use Akeneo\Category\Application\Query\DeleteCategoryTreeTemplate;
use Akeneo\Category\Application\Query\DeleteTemplateAndAttributes;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Query\GetEnrichedValuesByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CleanCategoryTemplateAndEnrichedValuesCommandHandlerSpec extends ObjectBehavior
{
    public function let(
        GetEnrichedValuesByTemplateUuid $getEnrichedValuesByTemplateUuid,
        CategoryAttributeValuesCleaner $categoryDataCleaner,
        GetAttribute $getCategoryTemplateAttributes,
        DeleteTemplateAndAttributes $deleteTemplateAndAttributes,
        DeleteCategoryTreeTemplate $categoryTreeTemplate
    ): void {
        $this->beConstructedWith(
            $getEnrichedValuesByTemplateUuid,
            $categoryDataCleaner,
            $getCategoryTemplateAttributes,
            $deleteTemplateAndAttributes,
            $categoryTreeTemplate
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CleanCategoryTemplateAndEnrichedValuesCommandHandler::class);
    }

    public function it_creates_and_saves_an_attribute(
        GetEnrichedValuesByTemplateUuid $getEnrichedValuesByTemplateUuid,
        CategoryAttributeValuesCleaner $categoryDataCleaner,
        GetAttribute $getCategoryTemplateAttributes,
        DeleteTemplateAndAttributes $deleteTemplateAndAttributes,
        DeleteCategoryTreeTemplate $categoryTreeTemplate,
    ): void {
        $templateUuidString = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $command = new CleanCategoryTemplateAndEnrichedValuesCommand($templateUuidString);
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $textSocks = AttributeText::create(
            uuid: AttributeUuid::fromString('ae7ab274-c7f8-11ed-afa1-0242ac120002'),
            code: new AttributeCode('text_socks'),
            order: AttributeOrder::fromInteger(1),
            isRequired: AttributeIsRequired::fromBoolean(false),
            isScopable: AttributeIsScopable::fromBoolean(true),
            isLocalizable: AttributeIsLocalizable::fromBoolean(true),
            labelCollection: LabelCollection::fromArray(['en_US' => 'URL slug']),
            templateUuid: $templateUuid,
            additionalProperties: AttributeAdditionalProperties::fromArray([]),
        );
        $attributeCollection = AttributeCollection::fromArray([$textSocks]);

        $getCategoryTemplateAttributes->byTemplateUuid($templateUuid)->shouldBeCalledOnce()->willReturn($attributeCollection);

        $valuesByCategoryCode = [
            [
                'socks' => $attributeCollection
            ]
        ];

        $getEnrichedValuesByTemplateUuid->byBatchesOf($templateUuid, 100)->willYield($valuesByCategoryCode);
        $categoryDataCleaner->cleanByTemplateAttributesUuid($valuesByCategoryCode[0], $attributeCollection->getAttributes())->shouldBeCalledOnce();

        $categoryTreeTemplate->byTemplateUuid($templateUuid)->shouldBeCalledOnce();
        $deleteTemplateAndAttributes->__invoke($templateUuid)->shouldBeCalledOnce();
        $this->__invoke($command);
    }
}
