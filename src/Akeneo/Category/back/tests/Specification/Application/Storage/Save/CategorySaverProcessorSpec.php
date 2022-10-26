<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\SetCode;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Storage\Save\CategorySaverRegistry;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySaverProcessorSpec extends ObjectBehavior
{
    function let(CategorySaverRegistry $categorySaverRegistry)
    {
        $this->beConstructedWith($categorySaverRegistry);
    }

    function it_uses_the_correct_savers_based_on_user_intent_list(
        CategorySaverRegistry $categorySaverRegistry,
        CategoryTranslationsSaver $categoryTranslationsSaver,
        Category $categoryModel
    )
    {
        $setLabelUserIntent = new SetLabel('en_US', 'socks');
        $categorySaverRegistry->fromUserIntent($setLabelUserIntent::class)->willReturn($categoryTranslationsSaver);
        $categoryTranslationsSaver->save($categoryModel)->shouldBeCalled();
        $this->save($categoryModel, [$setLabelUserIntent]);
    }

    function it_throws_an_exception_when_the_saver_class_was_not_added_into_the_savers_list(
        CategorySaverRegistry $categorySaverRegistry,
        Category $categoryModel,
        CategorySaver $unexpectedSaver,
        CategoryTranslationsSaver $categoryTranslationsSaver,
    )
    {
        $setLabelUserIntent = new SetLabel('en_US', 'socks');
        $setUnexpectedUserIntent = new SetLabel('fr_FR', 'bad label');
        $categorySaverRegistry->fromUserIntent($setLabelUserIntent::class)->willReturn($categoryTranslationsSaver);
        $categorySaverRegistry->fromUserIntent($setUnexpectedUserIntent::class)->willReturn($unexpectedSaver);
        $categoryTranslationsSaver->save($categoryModel)->shouldNotBeCalled();
        $unexpectedSaver->save($categoryModel)->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)
            ->duringSave($categoryModel, [$setLabelUserIntent]);
    }
}
