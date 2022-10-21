<?php

namespace Specification\Akeneo\Category\Application;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Query\IsCategoryTreeLinkedToTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Builder\TemplateBuilder;
use Akeneo\Category\Infrastructure\Storage\Sql\IsCategoryTreeLinkedToTemplateSql;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ActivateTemplateSpec extends ObjectBehavior
{
    function let(
        GetTemplate $getTemplate,
        GetCategoryInterface $getCategory,
        IsCategoryTreeLinkedToTemplateSql $isCategoryTreeLinkedToTemplateSql,
        TemplateBuilder $templateBuilder,
        CategoryTemplateSaver $templateSaver,
        CategoryTreeTemplateSaver $categoryTreeTemplateSaver,
    ) {
        $this->beConstructedWith(
            $getTemplate,
            $getCategory,
            $isCategoryTreeLinkedToTemplateSql,
            $templateBuilder,
            $templateSaver,
            $categoryTreeTemplateSaver,
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ActivateTemplate::class);
    }

    function it_insert_a_new_template_in_database (
        GetTemplate $getTemplate,
        GetCategoryInterface $getCategory,
        Category $categoryTree,
        IsCategoryTreeLinkedToTemplateSql $isCategoryTreeLinkedToTemplateSql,
        Template $templateModelToSave,
        TemplateBuilder $templateBuilder,
        CategoryTreeTemplateSaver $categoryTreeTemplateSaver,
        CategoryTemplateSaver $categoryTemplateSaver,
    )
    {
        $templateUuid = TemplateUuid::fromUuid(Uuid::uuid4());
        $categoryTreeId = new CategoryId(1);
        $templateCode = new TemplateCode('my_template');
        $templateLabelCollection = LabelCollection::fromArray(['en_US' => 'My Template']);

        $getCategory->byId($categoryTreeId->getValue())->willReturn($categoryTree);

        $isCategoryTreeLinkedToTemplateSql->__invoke($categoryTreeId)->willReturn(false);
        $categoryTree->getParentId()->willReturn(null);
        $categoryTree->getId()->willReturn($categoryTreeId);
        $getTemplate->exists($templateCode)->willReturn(false);

        $templateBuilder->generateTemplate(
            $categoryTree->getId(),
            $templateCode,
            $templateLabelCollection
        )->willReturn($templateModelToSave);

        $categoryTemplateSaver->insert($templateModelToSave)->shouldBeCalled();
        $categoryTreeTemplateSaver->linkAlreadyExists($templateModelToSave)->willReturn(false);
        $categoryTreeTemplateSaver->insert($templateModelToSave)->shouldBeCalled();
        $templateModelToSave->getUuid()->willReturn($templateUuid);

        $getTemplate->byUuid((string) $templateUuid)->shouldBeCalled();

        ($this)($categoryTreeId, $templateCode, $templateLabelCollection);
    }
/*
    function it_does_not_insert_a_already_existing_template_in_database (
        GetTemplate $getTemplate,
        Template $alreadyExistingTemplate,
        CategoryTemplateSaver $categoryTemplateSaver,
        CategoryTreeTemplateSaver $categoryTreeTemplateSaver,
        CategoryTemplateAttributeSaver $templateAttributeRepository,
    )
    {
        $templateUuid = TemplateUuid::fromUuid(Uuid::uuid4());
        $templateModel = new Template(
            $templateUuid,
            new TemplateCode('my_template'),
            LabelCollection::fromArray(['en_US' => 'My Template']),
            new CategoryId(1),
            AttributeCollection::fromArray([
                AttributeRichText::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('description'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'Description']),
                    $templateUuid
                ),
                AttributeImage::create(
                    AttributeUuid::fromUuid(Uuid::uuid4()),
                    new AttributeCode('banner_image'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsLocalizable::fromBoolean(false),
                    LabelCollection::fromArray(['en_US' => 'Banner image']),
                    $templateUuid
                )
            ])
        );

        $getTemplate->byUuid($templateModel->getUuid())->shouldBeCalled()->willReturn($alreadyExistingTemplate);
        $categoryTemplateSaver->insert($templateModel)->shouldNotBeCalled();
        $categoryTreeTemplateSaver->insert($templateModel)->shouldNotBeCalled();

        ($this)($templateModel);
    }
*/
}
