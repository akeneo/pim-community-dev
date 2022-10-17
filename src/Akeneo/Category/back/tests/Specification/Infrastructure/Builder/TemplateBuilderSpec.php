<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Builder;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
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

    public function it_generate_a_template_from_a_category_code(
        Code $searchingCategoryCode,
        GetCategoryInterface $getCategory,
        Category $categoryTree,
        Code $categoryTreeCode,
        CategoryId $categoryId
    )
    {
        $searchingCategoryCode->__toString()->willReturn('category_code');
        $getCategory->byCode('category_code')->shouldBeCalled()->willReturn($categoryTree);
        $categoryTree->getCode()->willReturn($categoryTreeCode);
        $categoryTreeCode->__toString()->willReturn('category_code');

        $labelCollection = LabelCollection::fromArray(['en_US' => 'Category code']);
        $categoryTree->getLabels()->willReturn($labelCollection);

        $categoryTree->getId()->willReturn($categoryId);
        $categoryId->getValue()->willReturn(1);

        $template = $this->generateTemplate($categoryTreeCode);

        $template->getCode()->__toString()->shouldReturn('category_code_template');
        $template->getLabelCollection()->getTranslation('en_US')->shouldReturn('Category code template');
    }
}
