<?php
declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Handler;

use Akeneo\Category\Application\Handler\GetPositionInterface;
use Akeneo\Category\Application\Query\GetDirectChildrenCategoryCodesInterface;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Infrastructure\Handler\GetPositionHandler;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPositionHandlerSpec extends ObjectBehavior
{
    function let(GetDirectChildrenCategoryCodesInterface $getDirectChildrenCategoryCodes)
    {
        $this->beConstructedWith($getDirectChildrenCategoryCodes);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(GetPositionInterface::class);
        $this->shouldHaveType(GetPositionHandler::class);
    }

    function it_gets_position_when_category_has_no_parent(Category $category)
    {
        $category->isRoot()->willReturn(true);

        $this->__invoke($category)->shouldReturn(1);
    }

    function it_gets_position(
        GetDirectChildrenCategoryCodesInterface $getDirectChildrenCategoryCodes,
        Category $category
    ) {
        $aCategoryCode = new Code('categoryC');
        $aCategoryParentId = new CategoryId(1);
        $aListOfParentCategoryChildren = [
            'categoryA' => ['row_num' => 1],
            'categoryB' => ['row_num' => 2],
            'categoryC' => ['row_num' => 3],
        ];

        $category->getCode()->willReturn($aCategoryCode);
        $category->isRoot()->willReturn(false);
        $category->getParentId()->willReturn($aCategoryParentId);

        $getDirectChildrenCategoryCodes->execute($aCategoryParentId->getValue())->willReturn($aListOfParentCategoryChildren);

        $this->__invoke($category)->shouldReturn(3);
    }
}
