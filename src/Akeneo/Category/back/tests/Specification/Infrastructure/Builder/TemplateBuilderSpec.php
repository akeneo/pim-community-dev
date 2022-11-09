<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Builder;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Infrastructure\Builder\TemplateBuilder;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TemplateBuilderSpec extends ObjectBehavior
{
    public function let(GetCategoryInterface $getCategory): void
    {
        $this->beConstructedWith($getCategory);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(TemplateBuilder::class);
    }

    public function it_generate_a_template_from_a_category_tree_id(
        GetCategoryInterface $getCategory,
        Category $categoryTree
    ) {
        $categoryTreeId = new CategoryId(1);
        $labelCollection = LabelCollection::fromArray(['en_US' => 'Category code']);

        $getCategory->byId($categoryTreeId->getValue())->shouldBeCalled()->willReturn($categoryTree);

        $categoryTree->getId()->willReturn($categoryTreeId);
        $categoryTree->getCode()->willReturn(new Code('category_code'));
        $categoryTree->getLabels()->willReturn($labelCollection);

        $template = $this->generateTemplate($categoryTreeId, new TemplateCode("unused_code"), LabelCollection::fromArray([]));

        $template->getCode()->__toString()->shouldReturn('category_code_template');
        $template->getLabelCollection()->getTranslation('en_US')->shouldReturn('Category code template');
    }

    /**  */
    public function it_generate_a_template_with_hard_coded_attributes(
        GetCategoryInterface $getCategory,
        Category $categoryTree
    ) {
        $categoryTreeId = new CategoryId(1);
        $labelCollection = LabelCollection::fromArray(['en_US' => 'Category code']);

        $getCategory->byId($categoryTreeId->getValue())->shouldBeCalled()->willReturn($categoryTree);

        $categoryTree->getId()->willReturn($categoryTreeId);
        $categoryTree->getCode()->willReturn(new Code('category_code'));
        $categoryTree->getLabels()->willReturn($labelCollection);

        $template = $this->generateTemplate($categoryTreeId, new TemplateCode("unused_code"), LabelCollection::fromArray([]));

        $template->getCode()->__toString()->shouldReturn('category_code_template');
        $template->getLabelCollection()->getTranslation('en_US')->shouldReturn('Category code template');

        $descriptionAttribute = $template->getAttributeCollection()->getAttributeByCode('description');
        $descriptionAttribute->getType()->__toString()->shouldReturn(AttributeType::TEXTAREA);
        $descriptionAttribute->getOrder()->intValue()->shouldReturn(1);
        $descriptionAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('Description');

        $imageAttribute = $template->getAttributeCollection()->getAttributeByCode('banner_image');
        $imageAttribute->getType()->__toString()->shouldReturn(AttributeType::IMAGE);
        $imageAttribute->getOrder()->intValue()->shouldReturn(2);
        $imageAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('Banner image');

        $metaTitleAttribute = $template->getAttributeCollection()->getAttributeByCode('seo_meta_title');
        $metaTitleAttribute->getType()->__toString()->shouldReturn(AttributeType::TEXT);
        $metaTitleAttribute->getOrder()->intValue()->shouldReturn(3);
        $metaTitleAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('SEO Meta Title');

        $metaDescriptionAttribute = $template->getAttributeCollection()->getAttributeByCode('seo_meta_description');
        $metaDescriptionAttribute->getType()->__toString()->shouldReturn(AttributeType::TEXT);
        $metaDescriptionAttribute->getOrder()->intValue()->shouldReturn(4);
        $metaDescriptionAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('SEO Meta Description');

        $keywordsAttribute = $template->getAttributeCollection()->getAttributeByCode('seo_keywords');
        $keywordsAttribute->getType()->__toString()->shouldReturn(AttributeType::TEXT);
        $keywordsAttribute->getOrder()->intValue()->shouldReturn(5);
        $keywordsAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('SEO Keywords');
    }
}
