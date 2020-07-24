<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Datagrid\Attribute;

use Akeneo\Pim\Structure\Bundle\Datagrid\Attribute\RegisterFamilyFilter;
use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Oro\Bundle\DataGridBundle\Common\IterableObject;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class RegisterFamilyFilterSpec extends ObjectBehavior
{
    public function let(TranslatedLabelsProviderInterface $familyRepository)
    {
        $this->beConstructedWith($familyRepository);
    }

    public function it_is_a_register_family_filter()
    {
        $this->shouldHaveType(RegisterFamilyFilter::class);
    }

    public function it_registers_the_family_filter(
        BuildBefore $event,
        IterableObject $config,
        $familyRepository
    ) {
        $familyRepository->findTranslatedLabels()->shouldBeCalled();
        $event->getConfig()->willReturn($config);
        $config->offsetAddToArrayByPath(FilterConfiguration::COLUMNS_PATH, Argument::type('array'))->shouldBeCalled();

        $this->buildBefore($event);
    }
}
