<?php

namespace Specification\Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Application\Query\DeleteCategoryTreeTemplate;
use Akeneo\Category\Application\Query\DeleteTemplateAndAttributes;
use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Component\Model\Category as LegacyCategory;
use Akeneo\Category\Infrastructure\EventSubscriber\RemoveCategoryTreeTemplateSubscriber;
use Akeneo\Category\Infrastructure\EventSubscriber\RemoveTemplateAndAttributesAfterCategoryTreeDeletionSubscriber;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveTemplateAndAttributesAfterCategoryTreeDeletionSubscriberSpec extends ObjectBehavior
{
    function let(
        FeatureFlag $enrichedCategoryFeature,
        DeleteTemplateAndAttributes $deleteTemplateAndAttributes
    ) {
        $this->beConstructedWith(
            $enrichedCategoryFeature,
            $deleteTemplateAndAttributes
        );
    }

    function it_is_initializable()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(RemoveTemplateAndAttributesAfterCategoryTreeDeletionSubscriber::class);
    }

    function it_triggers_the_delete_of_the_template_and_its_attributes(
        GenericEvent $event,
        LegacyCategory $legacyCategory,
        Template $template,
        FeatureFlag $enrichedCategoryFeature,
        DeleteTemplateAndAttributes $deleteTemplateAndAttributes

    ) {
        $event->getSubject()->willReturn($legacyCategory);
        $enrichedCategoryFeature->isEnabled()->willReturn(true);

        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');
        $template->getUuid()->willReturn($templateUuid);

        $deleteTemplateAndAttributes->__invoke($templateUuid)->shouldBeCalled();

        $event->getArguments()->shouldBeCalled()->willReturn(['templateUuid' => $templateUuid]);
        $this->removeTemplateAndAttributes($event);
    }

    function it_does_not_the_delete_of_the_template_and_its_attributes(
        GenericEvent $event,
        LegacyCategory $legacyCategory,
        Template $template,
        FeatureFlag $enrichedCategoryFeature,
        DeleteTemplateAndAttributes $deleteTemplateAndAttributes

    ) {
        $event->getSubject()->willReturn($legacyCategory);
        $enrichedCategoryFeature->isEnabled()->willReturn(false);

        $event->getArguments()->shouldNotBeCalled();
        $this->removeTemplateAndAttributes($event);
    }
}
