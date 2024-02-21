<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Datagrid\Attribute;

use Akeneo\Pim\Structure\Bundle\Datagrid\Attribute\RegisterFamilyFilter;
use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAllFamiliesLabelByLocaleQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Common\IterableObject;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PhpSpec\ObjectBehavior;

final class RegisterFamilyFilterSpec extends ObjectBehavior
{
    public function let(GetAllFamiliesLabelByLocaleQueryInterface $familiesLabelByLocaleQuery, UserContext $userContext)
    {
        $this->beConstructedWith($familiesLabelByLocaleQuery, $userContext);
    }

    public function it_is_a_register_family_filter()
    {
        $this->shouldHaveType(RegisterFamilyFilter::class);
    }

    public function it_registers_the_family_filter(
        BuildBefore $event,
        IterableObject $config,
        $familiesLabelByLocaleQuery,
        $userContext
    ) {
        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $familiesLabelByLocaleQuery->execute('en_US')->willReturn([
            'family1' => 'A family 1',
            'family2' => 'A family 2',
        ]);
        $event->getConfig()->willReturn($config);
        $config->offsetAddToArrayByPath(FilterConfiguration::COLUMNS_PATH, [
            'family' => [
                'type' => 'datagrid_attribute_family_filter',
                'ftype' => 'choice',
                'label' => 'Family',
                'data_name' => 'families',
                'options' => [
                    'field_options' => [
                        'multiple' => true,
                        'choices' => [
                            'A family 1' => 'family1',
                            'A family 2' => 'family2',
                        ],
                    ],
                ],
            ]
        ])->shouldBeCalled();

        $this->buildBefore($event);
    }
}
