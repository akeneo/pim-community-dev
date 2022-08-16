<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\SetCode;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Storage\Save\CategorySaverRegistry;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use Akeneo\Category\Domain\Model\Category;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProcessCategorySaveSpec extends ObjectBehavior
{
    function let(CategorySaverRegistry $categorySaverRegistry)
    {
        $this->beConstructedWith($categorySaverRegistry);
    }

    function it_uses_the_correct_savers_based_on_user_intent_list(
        CategorySaverRegistry $categorySaverRegistry,
        Category $categoryModel,
        CategoryBaseSaver $categoryBaseSaver,
        CategoryTranslationsSaver $categoryTranslationsSaver,
    )
    {
        $setCodeUserIntent = new SetCode('categoryCode');
        $setLabelUserIntent = new SetLabel('en_US', 'sausages');

        $categorySaverRegistry->fromUserIntent($setCodeUserIntent::class)->willReturn($categoryBaseSaver);
        $categorySaverRegistry->fromUserIntent($setLabelUserIntent::class)->willReturn($categoryTranslationsSaver);

        $categoryBaseSaver->save($categoryModel)->shouldBeCalled();
        $categoryTranslationsSaver->save($categoryModel)->shouldBeCalled();

        $this->save($categoryModel, [$setCodeUserIntent, $setLabelUserIntent]);
    }

    function it_throws_an_exception_when_the_saver_class_was_not_added_into_the_savers_list(
        CategorySaverRegistry $categorySaverRegistry,
        Category $categoryModel,
        CategorySaver $unexpectedSaver,
        CategoryBaseSaver $categoryBaseSaver,
        CategoryTranslationsSaver $categoryTranslationsSaver,
    )
    {
        $setCodeUserIntent = new SetCode('categoryCode');
        $setLabelUserIntent = new SetLabel('en_US', 'sausages');

        $categorySaverRegistry->fromUserIntent($setLabelUserIntent::class)->willReturn($categoryTranslationsSaver);
        $categorySaverRegistry->fromUserIntent($setCodeUserIntent::class)->willReturn($unexpectedSaver);

        $categoryTranslationsSaver->save($categoryModel)->shouldNotBeCalled();
        $unexpectedSaver->save($categoryModel)->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->during('save', [$categoryModel, [$setCodeUserIntent, $setLabelUserIntent]]);
    }
}
