<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorProductLinkRulesHydrator;
use PhpSpec\ObjectBehavior;

class ConnectorProductLinkRulesHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorProductLinkRulesHydrator::class);
    }

    function it_hydrates_rule_templates()
    {
        $ruleTemplates = [
            [
                'conditions' => [
                    [
                        'field' => 'sku',
                        'operator' => 'EQUALS',
                        'value' => '{{product_ref}}',
                    ],
                    [
                        'field' => 'countries',
                        'operator' => 'IN',
                        'value' => 'france, germany',
                        'locale' => 'fr_FR',
                    ]
                ],
                'actions' => [
                    [
                        'field' => 'user_instructions',
                        'type' => 'set',
                        'items' => ['{{code}}'],
                        'channel' => null,
                        'locale' => '{{locale}}',
                    ],
                    [
                        'field' => 'available',
                        'type' => 'add',
                        'items' => ['{{code}}'],
                        'channel' => null,
                        'locale' => null,
                    ],
                ],
            ]
        ];

        $expectedProductLinkRules = [
            [
                'product_selections' => [
                    [
                        'field' => 'sku',
                        'operator' => 'EQUALS',
                        'value' => '{{product_ref}}',
                    ],
                    [
                        'field' => 'countries',
                        'operator' => 'IN',
                        'value' => 'france, germany',
                        'locale' => 'fr_FR',
                    ],
                ],
                'assign_assets_to' => [
                    [
                        'attribute' => 'user_instructions',
                        'locale' => '{{locale}}',
                        'channel' => null,
                        'mode' => 'replace',
                    ],
                    [
                        'attribute' => 'available',
                        'locale' => null,
                        'channel' => null,
                        'mode' => 'add',
                    ],
                ],
            ],
        ];

        $this->hydrate($ruleTemplates)->shouldReturn($expectedProductLinkRules);
    }
}
