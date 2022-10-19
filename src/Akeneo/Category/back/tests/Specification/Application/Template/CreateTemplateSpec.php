<?php

namespace Specification\Akeneo\Category\Application\Template;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Template\CategoryTreeTemplateRepository;
use Akeneo\Category\Application\Template\CreateTemplate;
use Akeneo\Category\Application\Template\TemplateAttributeRepository;
use Akeneo\Category\Application\Template\TemplateRepository;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class CreateTemplateSpec extends ObjectBehavior
{
    function let(
        GetTemplate $getTemplate,
        TemplateRepository $templateRepository,
        CategoryTreeTemplateRepository $categoryTreeTemplateRepository,
        TemplateAttributeRepository $templateAttributeRepository,
    ) {
        $this->beConstructedWith(
            $getTemplate,
            $templateRepository,
            $categoryTreeTemplateRepository,
            $templateAttributeRepository,
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateTemplate::class);
    }

    function it_insert_a_new_template_in_database (
        GetTemplate $getTemplate,
        TemplateRepository $templateRepository,
        CategoryTreeTemplateRepository $categoryTreeTemplateRepository,
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

        $getTemplate->byUuid($templateModel->getUuid())->willReturn(null);
        $templateRepository->insert($templateModel)->shouldBeCalled();
        $categoryTreeTemplateRepository->linkAlreadyExists($templateModel)->willReturn(false);
        $categoryTreeTemplateRepository->insert($templateModel)->shouldBeCalled();

        ($this)($templateModel);
    }

    function it_does_not_insert_a_already_existing_template_in_database (
        GetTemplate $getTemplate,
        Template $alreadyExistingTemplate,
        TemplateRepository $templateRepository,
        CategoryTreeTemplateRepository $categoryTreeTemplateRepository,
        TemplateAttributeRepository $templateAttributeRepository,
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
        $templateRepository->insert($templateModel)->shouldNotBeCalled();
        $categoryTreeTemplateRepository->insert($templateModel)->shouldNotBeCalled();

        ($this)($templateModel);
    }

}
