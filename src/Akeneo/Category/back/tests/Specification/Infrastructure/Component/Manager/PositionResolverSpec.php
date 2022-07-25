<?php
declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Component\Manager;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Manager\PositionResolver;
use Akeneo\Category\Infrastructure\Component\Manager\PositionResolverInterface;
use Akeneo\Pim\Enrichment\Component\Category\Query\GetDirectChildrenCategoryCodesInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PositionResolverSpec extends ObjectBehavior
{
    function let(GetDirectChildrenCategoryCodesInterface $getDirectChildrenCategoryCodes)
    {
        $this->beConstructedWith($getDirectChildrenCategoryCodes);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(PositionResolverInterface::class);
        $this->shouldHaveType(PositionResolver::class);
    }

    function it_gets_position_when_category_has_no_parent(CategoryInterface $category)
    {
        $category->isRoot()->willReturn(true);

        $this->getPosition($category)->shouldReturn(1);
    }

    function it_gets_position(
        GetDirectChildrenCategoryCodesInterface $getDirectChildrenCategoryCodes,
        CategoryInterface $category,
        CategoryInterface $categoryParent
    ) {
        $aCategoryCode = 'categoryC';
        $aCategoryParentId = 1;
        $aListOfParentCategoryChildren = [
            'categoryA' => ['row_num' => 1],
            'categoryB' => ['row_num' => 2],
            'categoryC' => ['row_num' => 3],
        ];

        $category->getCode()->willReturn($aCategoryCode);
        $category->isRoot()->willReturn(false);
        $category->getParent()->willReturn($categoryParent);
        $categoryParent->getId()->willReturn($aCategoryParentId);

        $getDirectChildrenCategoryCodes->execute($aCategoryParentId)->willReturn($aListOfParentCategoryChildren);

        $this->getPosition($category)->shouldReturn(3);
    }
}
