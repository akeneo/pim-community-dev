<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\ProjectItemCalculatorInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\AddUserGroupStep;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;

class AddUserGroupStepSpec extends ObjectBehavior
{
    function let(ProjectItemCalculatorInterface $contributorGroupCalculator)
    {
        $this->beConstructedWith($contributorGroupCalculator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddUserGroupStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_adds_to_the_project_the_user_group_that_have_edit_permission_on_categories_and_attribute_groups(
        $contributorGroupCalculator,
        ProjectInterface $project,
        ProductInterface $product,
        Group $userGroup,
        Group $otherUserGroup,
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);

        $contributorGroupCalculator->calculate($product, $channel, $locale)->willReturn([$userGroup]);

        $project->addUserGroup($userGroup)->shouldBeCalled();
        $project->addUserGroup($otherUserGroup)->shouldNotBeCalled();

        $this->execute($product, $project);
    }
}
