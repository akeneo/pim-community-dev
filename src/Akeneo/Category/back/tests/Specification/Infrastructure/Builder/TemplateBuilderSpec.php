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

    public function it_generates_a_template_from_a_category_tree_id(
        GetCategoryInterface $getCategory,
        Category $categoryTree
    ): void {
        $categoryTreeId = new CategoryId(1);
        $labelCollection = LabelCollection::fromArray(['en_US' => 'Category code']);

        $getCategory->byId($categoryTreeId->getValue())->shouldBeCalled()->willReturn($categoryTree);

        $categoryTree->getId()->willReturn($categoryTreeId);
        $categoryTree->getCode()->willReturn(new Code('category_code'));
        $categoryTree->getLabels()->willReturn($labelCollection);

        $template = $this->generateTemplate($categoryTreeId, new TemplateCode('template_code'), LabelCollection::fromArray([]));

        $template->getCode()->__toString()->shouldReturn('template_code');
    }

    /**  */
    public function it_generates_a_template_with_hard_coded_attributes(
        GetCategoryInterface $getCategory,
        Category $categoryTree
    ): void {
        $categoryTreeId = new CategoryId(1);
        $labelCollection = LabelCollection::fromArray(['en_US' => 'Category code']);

        $getCategory->byId($categoryTreeId->getValue())->shouldBeCalled()->willReturn($categoryTree);

        $categoryTree->getId()->willReturn($categoryTreeId);
        $categoryTree->getCode()->willReturn(new Code('category_code'));
        $categoryTree->getLabels()->willReturn($labelCollection);

        $template = $this->generateTemplate($categoryTreeId, new TemplateCode('template_code'), LabelCollection::fromArray([]));

        $template->getCode()->__toString()->shouldReturn('template_code');
//        TODO: will be moved to test of service that will load the predefined attributes in GRF-842
//        $richTextAttribute = $template->getAttributeCollection()->getAttributeByCode('long_description');
//        $richTextAttribute->getType()->__toString()->shouldReturn(AttributeType::RICH_TEXT);
//        $richTextAttribute->getOrder()->intValue()->shouldReturn(1);
//        $richTextAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('Long description');
//
//        $textAttribute = $template->getAttributeCollection()->getAttributeByCode('url_slug');
//        $textAttribute->getType()->__toString()->shouldReturn(AttributeType::TEXT);
//        $textAttribute->getOrder()->intValue()->shouldReturn(3);
//        $textAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('URL slug');
//
//        $imageAttribute = $template->getAttributeCollection()->getAttributeByCode('image_1');
//        $imageAttribute->getType()->__toString()->shouldReturn(AttributeType::IMAGE);
//        $imageAttribute->getOrder()->intValue()->shouldReturn(4);
//        $imageAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('Image 1');
//
//        $textAreaAttribute = $template->getAttributeCollection()->getAttributeByCode('seo_meta_description');
//        $textAreaAttribute->getType()->__toString()->shouldReturn(AttributeType::TEXTAREA);
//        $textAreaAttribute->getOrder()->intValue()->shouldReturn(11);
//        $textAreaAttribute->getLabelCollection()->getTranslation('en_US')->shouldReturn('SEO meta description');
    }
}
