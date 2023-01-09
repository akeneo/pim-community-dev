<?php

namespace Specification\Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Application\Query\DeleteCategoryTreeTemplate;
use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Component\Model\Category as LegacyCategory;
use Akeneo\Category\Infrastructure\EventSubscriber\RemoveCategoryTreeTemplateSubscriber;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCategoryTreeTemplateSubscriberSpec extends ObjectBehavior
{
    function let(
        GetCategoryInterface $getCategory,
        GetCategoryTemplateByCategoryTree $getCategoryTemplateByCategoryTree,
        DeleteCategoryTreeTemplate $deleteCategoryTreeTemplate,
        FeatureFlag $enrichedCategoryFeature
    ) {
        $this->beConstructedWith(
            $getCategory,
            $getCategoryTemplateByCategoryTree,
            $deleteCategoryTreeTemplate,
            $enrichedCategoryFeature
        );
    }

    function it_is_initializable()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(RemoveCategoryTreeTemplateSubscriber::class);
    }

    function it_triggers_the_delete_of_the_link_with_template_before_category_removal(
        GenericEvent $event,
        LegacyCategory $legacyCategory,
        Category $category,
        Template $template,
        GetCategoryInterface $getCategory,
        GetCategoryTemplateByCategoryTree $getCategoryTemplateByCategoryTree,
        DeleteCategoryTreeTemplate $deleteCategoryTreeTemplate,
        FeatureFlag $enrichedCategoryFeature
    ) {
        $event->getSubject()->willReturn($legacyCategory);
        $enrichedCategoryFeature->isEnabled()->willReturn(true);

        $legacyCategory->getId()->willReturn(1);
        $getCategory->byId(1)->shouldBeCalled()->willReturn($category);

        $categoryTreeId = new CategoryId(1);
        $category->getId()->willReturn($categoryTreeId);
        $category->isRoot()->willReturn(true);

        $getCategoryTemplateByCategoryTree->__invoke($categoryTreeId)->shouldBeCalled()->willReturn($template);

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $template->getUuid()->willReturn($templateUuid);

        $deleteCategoryTreeTemplate->__invoke(1, $templateUuid)->shouldBeCalled();

        $this->removeCategoryTreeTemplate($event);
    }

    function it_does_not_trigger_the_delete_of_the_link_with_template_before_category_removal_when_feature_flag_is_not_activated(
        GenericEvent $event,
        LegacyCategory $legacyCategory,
        DeleteCategoryTreeTemplate $deleteCategoryTreeTemplate,
        FeatureFlag $enrichedCategoryFeature
    ) {
        $event->getSubject()->willReturn($legacyCategory);
        $enrichedCategoryFeature->isEnabled()->willReturn(false);

        $deleteCategoryTreeTemplate->__invoke(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->removeCategoryTreeTemplate($event);
    }

    function it_does_not_trigger_the_delete_of_the_link_with_template_before_category_removal_when_category_is_not_the_root(
        GenericEvent $event,
        LegacyCategory $legacyCategory,
        Category $category,
        GetCategoryInterface $getCategory,
        DeleteCategoryTreeTemplate $deleteCategoryTreeTemplate,
        FeatureFlag $enrichedCategoryFeature
    ) {
        $event->getSubject()->willReturn($legacyCategory);
        $enrichedCategoryFeature->isEnabled()->willReturn(true);

        $legacyCategory->getId()->willReturn(1);
        $getCategory->byId(1)->shouldBeCalled()->willReturn($category);

        $categoryTreeId = new CategoryId(1);
        $category->getId()->willReturn($categoryTreeId);
        $category->isRoot()->willReturn(false);

        $deleteCategoryTreeTemplate->__invoke(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->removeCategoryTreeTemplate($event);
    }

    function it_does_not_trigger_the_delete_of_the_link_with_template_before_category_removal_when_there_is_no_template(
        GenericEvent $event,
        LegacyCategory $legacyCategory,
        Category $category,
        GetCategoryInterface $getCategory,
        GetCategoryTemplateByCategoryTree $getCategoryTemplateByCategoryTree,
        DeleteCategoryTreeTemplate $deleteCategoryTreeTemplate,
        FeatureFlag $enrichedCategoryFeature
    ) {
        $event->getSubject()->willReturn($legacyCategory);
        $enrichedCategoryFeature->isEnabled()->willReturn(true);

        $legacyCategory->getId()->willReturn(1);
        $getCategory->byId(1)->shouldBeCalled()->willReturn($category);

        $categoryTreeId = new CategoryId(1);
        $category->getId()->willReturn($categoryTreeId);
        $category->isRoot()->willReturn(true);

        $getCategoryTemplateByCategoryTree->__invoke($categoryTreeId)->shouldBeCalled()->willReturn(null);

        $deleteCategoryTreeTemplate->__invoke(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->removeCategoryTreeTemplate($event);
    }
}
